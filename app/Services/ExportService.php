<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExportService
{
    static function getData(String $content)
    {
        $content = collect(json_decode($content));
        $nodes = collect($content['nodeDataArray']);
        $relations = collect($content['linkDataArray']);

        $fragments = self::getFragments($nodes);
        $objects = self::getObjects($nodes);
        $code = '';

        $data = [];
        foreach ($objects as $object) {
            $messages = $relations->where('from', $object->key)->values();
            $methods = $messages->map(function ($message) use ($fragments, $nodes) {
                $loop = false;
                $opt = false;

                foreach ($fragments as $fragment) {
                    if ($fragment['relatedElement']->key ==  $message->key) {

                        switch ($fragment['category']) {
                            case 'loop':
                                $loop = $fragment['text'];
                                break;
                            case 'opt':
                                $opt = $fragment['text'];
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
                return $method;
            });
            $data[] = ['object' => $object->text, 'methods' => $methods];
        }

        return true;
    }

    static function getFragments(Collection $nodes): Collection
    {
        $allFragments = $nodes->whereIn('category', ['alt', 'opt', 'loop']);
        $fragments = collect();
        foreach ($allFragments as $fragment) {
            $pos = explode(" ", $fragment->loc);
            $posX = $pos[0];
            $posY = $pos[1];
            $fragments->push(collect([
                'category' => $fragment->category,
                'key' => $fragment->key,
                'text' => $fragment->text ?? '',
                'text2' => $fragment->text2 ?? '',
                'relatedElement' => collect($fragment->relatedElements)->sortBy('time')->first(),
                'posX' => $posX,
                'posY' => $posY,
                'width' => $fragment->width,
                'height' => $fragment->height,
            ]));
        }

        return $fragments;
    }

    static function getObjects(Collection $nodes): Collection
    {
        $objects = collect();
        foreach ($nodes as $node) {
            if (!empty($node->category) && $node->category != 'opt' && $node->category != 'alt' && $node->category != 'loop') {
                $pos = explode(" ", $node->loc);
                $posX = $pos[0];
                $posY = $pos[1];
                $objects->push(collect([
                    'category' => $node->category,
                    'key' => $node->key,
                    'text' => $node->text ?? '',
                    'duration' => $node->duration,
                    'posX' => $posX,
                    'posY' => $posY,
                ]));
            }
        }
        return $objects;
    }


    static function toCamelCase($input): string
    {
        $words = explode(' ', $input);
        $words[0] = strtolower($words[0]);

        for ($i = 1; $i < count($words); $i++) {
            $words[$i] = ucfirst($words[$i]);
        }

        $camelCaseString = implode('', $words);

        return $camelCaseString;
    }

    static function toPascalCase($input): string
    {
        $words = explode(' ', $input);

        $pascalCaseString = '';
        foreach ($words as $word) {
            $pascalCaseString .= ucfirst($word);
        }

        return $pascalCaseString;
    }
}
