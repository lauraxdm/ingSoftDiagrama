<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExportCodeService extends ExportService
{
    static function generateClass(string $language, string $className, Collection $methods): string
    {
        $code = "class $className {\n";
        if ($language == "cpp") {
            if ($methods->count() > 0) {
                $code .= "public:\n";
            }

        }

        foreach ($methods as $method) {
            $methodName = $method['name'];
            $externalClass = $method['external_class'];
            $externalClass .= $language == 'cpp' ? '*' : '';
            $object = $language == 'php' ? '$' : '';
            $object .= lcfirst($method['external_class']);

            switch ($language) {
                case 'java':
                    $code .= "\tpublic void $methodName ";
                    break;
                case 'php':
                    $code .= "\tpublic function $methodName ";
                    break;
                case 'cpp':
                    $code .= "\tvoid $methodName";
                    break;
                default:
                    # code...
                    break;
            }

            $code .= "($externalClass $object) {\n";

            if ($method['loop']) {
                $condition = str_replace(['[', ']'], '', $method['loop']);
                if ($language == 'php') {
                    $condition = self::conditionToPHP($condition);
                    $vars = self::getVarsFromPHPCondition($condition);

                    foreach ($vars as $var) {
                        $code .= "\t\t$var = '';\n";
                    }
                }
                $code .= "\t\twhile ($condition) {\n";
                $code .= "\t\t\t// Código del loop\n";
                $code .= "\t\t}\n";
            }
            if ($method['opt']) {
                $condition = str_replace(['[', ']'], '', $method['opt']);
                if ($language == 'php') {
                    $condition = self::conditionToPHP($condition);
                    $vars = self::getVarsFromPHPCondition($condition);
                    foreach ($vars as $var) {
                        $code .= "\t\t$var = '';\n";
                    }
                }
                $code .= "\t\tif ($condition) {\n";
                $code .= "\t\t\t// Código del fragmento opt\n";
                $code .= "\t\t}\n";
            }
            if ($method['alt']) {
                $condition = str_replace(['[', ']'], '', $method['loop']);
                if ($language == 'php') {
                    $condition = self::conditionToPHP($condition);
                    $vars = self::getVarsFromPHPCondition($condition);
                    foreach ($vars as $var) {
                        $code .= "\t\t$var = '';\n";
                    }
                }
                $code .= "\t\tif ($condition) {\n";
                $code .= "\t\t\t// Código del fragmento\n";
                $code .= "\t\t}\n";
            }
            $code .= "\t\t// Código del método $methodName\n";


            $code .= "\t}\n";
        }
        $code .= "}\n\n";
        return $code;
    }

    static function conditionToPHP(string $condition): string
    {
        $result = preg_replace_callback('/\b(\w+)\b/', function ($matches) {
            if (is_numeric($matches[1])) {
                return $matches[1];
            } else {
                return '$' . $matches[1];
            }
        }, $condition);

        return $result;
    }


    static function getVarsFromPHPCondition(string $condition): array
    {
        $vars = array();
        $salida = preg_replace_callback('/\$\w+/', function ($matches) use (&$vars) {
            $vars[] = $matches[0];
            return $matches[0];
        }, $condition);

        return $vars;
    }

    static function generateCode(string $language, string $diagramName, string $content): string
    {
        $diagramName = self::toPascalCase($diagramName);

        $content = collect(json_decode($content));
        $nodes = collect($content['nodeDataArray']);
        $relations = collect($content['linkDataArray']);

        $fragments = self::getFragments($nodes);
        $objects = self::getObjects($nodes);

        $code = self::getHead($language);

        foreach ($objects as $object) {
            $messages = $relations->where('from', $object['key'])->values();
            $methods = $messages->map(function ($message) use ($fragments, $nodes) {
                $loop = false;
                $opt = false;
                $alt = false;

                foreach ($fragments as $fragment) {
                    if (!empty($fragment['relatedElement']->key) && $fragment['relatedElement']->key ==  $message->key) {
                        switch ($fragment['category']) {
                            case 'loop':
                                $loop = $fragment['text'];
                                break;
                            case 'opt':
                                $opt = $fragment['text'];
                                break;
                            case 'alt':
                                $alt = $fragment['text'];
                                break;
                            default:
                                # code...
                                break;
                        }
                    }
                }

                $method = [];
                $method['name'] = self::toCamelCase($message->text);
                $method['external_class'] = self::toPascalCase($nodes->firstWhere('key', $message->to)->text);
                $method['loop'] = $loop;
                $method['opt'] = $opt;
                $method['alt'] = $alt;
                return $method;
            });
            $objectName = self::toPascalCase($object['text']);
            $code .= self::generateClass($language, $objectName, $methods);
        }

        $code .= self::getMainClass($language, $diagramName);

        //dd($code);
        return $code;
    }

    static function getHead(string $language): string
    {
        $code = '';
        switch ($language) {
            case 'java':
                $code .= "import java.util.*;\n";
                $code .= "import java.io.*;\n\n";
                break;
            case 'php':
                $code .= "<?php\n\n";
                break;
            case 'cpp':
                $code .= "#include <iostream>\n\n";
                break;
            default:
                # code...
                break;
        }
        return $code;
    }

    static function getMainClass(string $language, string $className): string
    {
        $code = '';
        switch ($language) {
            case 'java':
                $code = "\npublic class $className {\n";
                $code .= "\tpublic static void main(string[] args) {\n";
                $code .= "\t\t// Código para la aplicación Java\n";
                $code .= "\t}\n";
                $code .= "}\n";
                break;
            case 'php':
                $code = "\nclass $className {\n";
                $code .= "\tpublic static function main(" . '$args' . ") {\n";
                $code .= "\t\t// Código para la aplicación PHP\n";
                $code .= "\t}\n";
                $code .= "}\n";
                break;
            case 'cpp':
                $code = "int main() {\n";
                $code .= "\t// Código para la aplicación C++\n";
                $code .= "\treturn 0;\n";
                $code .= "}\n";
                break;
            default:
                # code...
                break;
        }
        return $code;
    }
}
