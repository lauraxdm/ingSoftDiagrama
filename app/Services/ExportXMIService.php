<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExportXMIService extends ExportService
{
    static function generate(string $diagramName, string $content): string
    {
        $content = collect(json_decode($content));
        $nodes = collect($content['nodeDataArray']);
        $messages = Collection::make(json_decode(json_encode($content['linkDataArray']), false))->sortBy('time');

        $fragments = self::getFragments($nodes);
        $all_objects = self::getObjects($nodes);
        $objects = $all_objects->where('category', '!=', 'actor');
        $actors = $all_objects->where('category', '==', 'actor');

        $script = '<?xml version="1.0" encoding="windows-1252"?>' . "\n";
        $script .= '<XMI xmi.version="1.1" xmlns:UML="omg.org/UML1.3" timestamp="2023-10-14 17:57:11">' . "\n";
        $script .= '<XMI.header>' . "\n";
        $script .= '<XMI.documentation>' . "\n";
        $script .= '<XMI.exporter>Enterprise Architect</XMI.exporter><XMI.exporterVersion>2.5</XMI.exporterVersion><XMI.exporterID>1554</XMI.exporterID>' . "\n";
        $script .= '</XMI.documentation>' . "\n";
        $script .= '</XMI.header>' . "\n";
        $script .= '<XMI.content>' . "\n";
        $script .= '<UML:Model name="EA Model" xmi.id="eamodel">' . "\n";
        $script .= '<UML:Namespace.ownedElement>' . "\n";
        $script .= '<UML:Class name="EARootClass" xmi.id="id_root_class" isRoot="true" isLeaf="false" isAbstract="false"/>' . "\n";
        $script .= '<UML:Package name="' . $diagramName . '" xmi.id="id_package" isRoot="false" isLeaf="false" isAbstract="false" visibility="public">' . "\n";
        $script .= '<UML:ModelElement.taggedValue>
                        <UML:TaggedValue tag="parent" value="EAPK_D24CA985_33A5_41b1_87BB_49FE0EAF9F6A"/>
                        <UML:TaggedValue tag="ea_package_id" value="10"/>
                        <UML:TaggedValue tag="created" value="2023-10-14 16:08:39"/>
                        <UML:TaggedValue tag="modified" value="' . date('Y-m-d H:i:s') . '"/>
                        <UML:TaggedValue tag="iscontrolled" value="FALSE"/>
                        <UML:TaggedValue tag="lastloaddate" value="2023-10-14 16:08:39"/>
                        <UML:TaggedValue tag="lastsavedate" value="2023-10-14 16:08:39"/>
                        <UML:TaggedValue tag="version" value="1.0"/>
                        <UML:TaggedValue tag="isprotected" value="FALSE"/>
                        <UML:TaggedValue tag="usedtd" value="FALSE"/>
                        <UML:TaggedValue tag="logxml" value="FALSE"/>
                        <UML:TaggedValue tag="tpos" value="1"/>
                        <UML:TaggedValue tag="packageFlags" value="CRC=0;"/>
                        <UML:TaggedValue tag="batchsave" value="0"/>
                        <UML:TaggedValue tag="batchload" value="0"/>
                        <UML:TaggedValue tag="phase" value="1.0"/>
                        <UML:TaggedValue tag="status" value="Proposed"/>
                        <UML:TaggedValue tag="author" value="Usuario"/>
                        <UML:TaggedValue tag="complexity" value="1"/>
                        <UML:TaggedValue tag="ea_stype" value="Public"/>
                        <UML:TaggedValue tag="tpos" value="1"/>
                        <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                    </UML:ModelElement.taggedValue>' . "\n";
        $script .= '<UML:Namespace.ownedElement>' . "\n";
        $script .= '<UML:Collaboration xmi.id="id_collaboration" name="Collaborations">' . "\n";
        $script .= '<UML:Namespace.ownedElement>' . "\n";

        foreach ($objects as $object) {

            $script .= '<UML:ClassifierRole name="' . $object['text'] . '" xmi.id="' . $object['key'] . '" visibility="public" base="id_root_class">' . "\n";

            switch ($object['category']) {
                case 'boundary':
                    $script .= '<UML:ModelElement.stereotype>' . "\n" .
                        '<UML:Stereotype name="boundary"/>
                                </UML:ModelElement.stereotype>' . "\n";
                    break;
                case 'control':
                    $script .= '<UML:ModelElement.stereotype>' . "\n" .
                        '<UML:Stereotype name="control"/>
                                </UML:ModelElement.stereotype>' . "\n";
                    break;
                case 'entity':
                    $script .= '<UML:ModelElement.stereotype>' . "\n" .
                        '<UML:Stereotype name="entity"/>
                                </UML:ModelElement.stereotype>' . "\n";
                    break;
                default:
                    # code...
                    break;
            }
            $script .= '<UML:ModelElement.taggedValue>' . "\n";
            $script .= '<UML:TaggedValue tag="isAbstract" value="false"/>
                        <UML:TaggedValue tag="isSpecification" value="false"/>
                        <UML:TaggedValue tag="ea_stype" value="Sequence"/>
                        <UML:TaggedValue tag="ea_ntype" value="0"/>' . "\n";
            $script .= '<UML:TaggedValue tag="version" value="1.0"/>
                        <UML:TaggedValue tag="isActive" value="false"/>
                        <UML:TaggedValue tag="package" value="id_package"/>
                        <UML:TaggedValue tag="date_created" value="' . date('Y-m-d H:i:s') . '"/>' . "\n";
            $script .= '<UML:TaggedValue tag="date_modified" value="' . date('Y-m-d H:i:s') . '"/>
                        <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                        <UML:TaggedValue tag="tagged" value="0"/>
                        <UML:TaggedValue tag="package_name" value="' . $diagramName . '"/>' . "\n";
            $script .= '<UML:TaggedValue tag="phase" value="1.0"/>
                        <UML:TaggedValue tag="author" value="Usuario"/>
                        <UML:TaggedValue tag="complexity" value="1"/>
                        <UML:TaggedValue tag="status" value="Proposed"/>
                        <UML:TaggedValue tag="tpos" value="0"/>' . "\n";
            $script .= '<UML:TaggedValue tag="ea_localid" value="' . $object['key'] . '"/>
                        <UML:TaggedValue tag="ea_eleType" value="element"/>
                        <UML:TaggedValue tag="style" value="BackColor=-1;BorderColor=-1;BorderWidth=-1;FontColor=-1;VSwimLanes=1;HSwimLanes=1;BorderStyle=0;"/>
                        ' . "\n";
            switch ($object['category']) {
                case 'boundary':
                    $script .= '<UML:TaggedValue tag="$ea_xref_property" value="$XREFPROP=$XID={5348FF77-943A-444a-9CCF-93DF4BC61AFA}$XID;$NAM=Stereotypes$NAM;$TYP=element property$TYP;$VIS=Public$VIS;$PAR=0$PAR;$DES=@STEREO;Name=boundary;FQName=EAUML::boundary;@ENDSTEREO;$DES;$CLT={97EAF158-EDD9-49d4-8F1D-104C43604D20}$CLT;$SUP=&lt;none&gt;$SUP;$ENDXREF;"/>' . "\n";
                    break;
                case 'control':
                    $script .= '<UML:TaggedValue tag="$ea_xref_property" value="$XREFPROP=$XID={267300E6-C73B-4a89-A2CA-4684AFAE6344}$XID;$NAM=Stereotypes$NAM;$TYP=element property$TYP;$VIS=Public$VIS;$PAR=0$PAR;$DES=@STEREO;Name=control;FQName=EAUML::control;@ENDSTEREO;$DES;$CLT={CB1541F9-CC8D-4438-83F4-93CAEB48F0F8}$CLT;$SUP=&lt;none&gt;$SUP;$ENDXREF;"/>' . "\n";
                    break;
                case 'entity':
                    $script .= '<UML:TaggedValue tag="$ea_xref_property" value="$XREFPROP=$XID={9E1AD596-967E-4ba2-8994-3FC7B78EF81C}$XID;$NAM=Stereotypes$NAM;$TYP=element property$TYP;$VIS=Public$VIS;$PAR=0$PAR;$DES=@STEREO;Name=entity;FQName=EAUML::entity;@ENDSTEREO;$DES;$CLT={AECFD021-C222-44cd-96F7-8F3BBE9666BD}$CLT;$SUP=&lt;none&gt;$SUP;$ENDXREF;"/>' . "\n";
                    break;
                default:
                    # code...
                    break;
            }
            $script .= '</UML:ModelElement.taggedValue>' . "\n";
            $script .= '</UML:ClassifierRole>' . "\n";
        }
        $script .= '</UML:Namespace.ownedElement>' . "\n";
        //relations
        $script .= '<UML:Collaboration.interaction>' . "\n";
        $script .= '<UML:Interaction xmi.id="id_interaction" name="id_interaction">' . "\n";
        $script .= '<UML:Interaction.message>' . "\n";
        $numMessage = 1;

        $lifeTimes = collect();

        foreach ($messages as $message) {
            $isFirst = false;
            $message = $message;

            $objectFrom = $all_objects->firstWhere('key', $message->from);
            $objectTo = $all_objects->firstWhere('key', $message->to);

            $minX = ($objectFrom['posX'] <= $objectTo['posX']) ? $objectFrom['posX'] : $objectTo['posX'];
            $maxX = ($objectFrom['posX'] > $objectTo['posX']) ? $objectFrom['posX'] : $objectTo['posX'];

            $objectsMiddle = $objects->where('posX', '>=', $minX)->where('posX', '<=', $maxX)->pluck('key');
            //dump($objectsMiddle);

            $maxLifeTime = $lifeTimes->only($objectsMiddle)->max();

            if ($maxLifeTime != null) {
                $h = $message->time - $maxLifeTime;
            } else {
                $h = $message->time - 8;
                $isFirst = true;
            }

            foreach ($objectsMiddle as $object) {
                $lifeTimes = $lifeTimes->merge([$object => $message->time]);
            }

            //dump($message->time);
            //dump($h);


            $initialMargin = $isFirst ? 10.0 : 35.0;
            $h = (-$h * 15.372) + $initialMargin;
            // dump($h);
            //dump('-------------');
            $script .= self::generateMessage($message, $numMessage, $h);

            $numMessage++;
        }
        //dump($lifeTimes);
        //dd($script);
        $script .= '</UML:Interaction.message>' . "\n";
        $script .= '</UML:Interaction>' . "\n";
        $script .= '</UML:Collaboration.interaction>' . "\n";
        $script .= '</UML:Collaboration>' . "\n";
        //actors
        foreach ($actors as $actor) {
            $script .= '<UML:Actor name="' . $actor['text'] . '" xmi.id="' . $actor['key'] . '" visibility="public" namespace="id_package" isRoot="false" isLeaf="false" isAbstract="false">' . "\n";
            $script .= '<UML:ModelElement.taggedValue>' . "\n";
            $script .= '<UML:TaggedValue tag="isSpecification" value="false"/>
                        <UML:TaggedValue tag="ea_stype" value="Actor"/>
                        <UML:TaggedValue tag="ea_ntype" value="0"/>
                        <UML:TaggedValue tag="version" value="1.0"/>' . "\n";
            $script .= '<UML:TaggedValue tag="isActive" value="false"/>
                        <UML:TaggedValue tag="package" value="id_package"/>
                        <UML:TaggedValue tag="date_created" value="2023-10-14 16:08:39"/>
                        <UML:TaggedValue tag="date_modified" value="' . date('Y-m-d H:i:s') . '"/>' . "\n";
            $script .= '<UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                        <UML:TaggedValue tag="tagged" value="0"/>
                        <UML:TaggedValue tag="package_name" value="' . $diagramName . '"/>
                        <UML:TaggedValue tag="phase" value="1.0"/>' . "\n";
            $script .= '<UML:TaggedValue tag="author" value="Usuario"/>
                        <UML:TaggedValue tag="complexity" value="1"/>
                        <UML:TaggedValue tag="status" value="Proposed"/>
                        <UML:TaggedValue tag="tpos" value="0"/>' . "\n";
            $script .= '<UML:TaggedValue tag="ea_localid" value="' . $actor['key'] . '"/>
                        <UML:TaggedValue tag="ea_eleType" value="element"/>
                        <UML:TaggedValue tag="style" value="BackColor=-1;BorderColor=-1;BorderWidth=-1;FontColor=-1;VSwimLanes=1;HSwimLanes=1;BorderStyle=0;"/>' . "\n";
            $script .= '</UML:ModelElement.taggedValue>' . "\n";
            $script .= '</UML:Actor>' . "\n";
        }
        //fragments
        foreach ($fragments as $fragment) {
            $script .= self::generateFragment($fragment, $diagramName);
        }

        $script .= '</UML:Namespace.ownedElement>' . "\n";
        $script .= '</UML:Package>' . "\n";
        $script .= '</UML:Namespace.ownedElement>' . "\n";
        $script .= '</UML:Model>' . "\n";
        //footer
        $script .= '<UML:Diagram name="' . $diagramName . '" xmi.id="id_diagram" diagramType="SequenceDiagram" owner="id_package" toolName="Enterprise Architect 2.5">' . "\n";
        $script .= '<UML:ModelElement.taggedValue>
                        <UML:TaggedValue tag="version" value="1.0"/>
                        <UML:TaggedValue tag="author" value="Usuario"/>
                        <UML:TaggedValue tag="created_date" value="2023-10-14 16:08:39"/>
                        <UML:TaggedValue tag="modified_date" value="' . date('Y-m-d H:i:s') . '"/>
                        <UML:TaggedValue tag="package" value="id_package"/>
                        <UML:TaggedValue tag="type" value="Sequence"/>
                        <UML:TaggedValue tag="swimlanes" value="locked=false;orientation=0;width=0;inbar=false;names=false;color=-1;bold=false;fcol=0;tcol=-1;ofCol=-1;ufCol=-1;hl=0;ufh=0;hh=0;cls=0;bw=0;hli=0;SwimlaneFont=lfh:-13,lfw:0,lfi:0,lfu:0,lfs:0,lfface:Calibri,lfe:0,lfo:0,lfchar:1,lfop:0,lfcp:0,lfq:0,lfpf=0,lfWidth=0;"/>
                        <UML:TaggedValue tag="matrixitems" value="locked=false;matrixactive=false;swimlanesactive=true;kanbanactive=false;width=1;clrLine=0;"/>
                        <UML:TaggedValue tag="ea_localid" value="9"/>
                        <UML:TaggedValue tag="EAStyle" value="ShowPrivate=1;ShowProtected=1;ShowPublic=1;HideRelationships=0;Locked=0;Border=1;HighlightForeign=1;PackageContents=1;SequenceNotes=0;ScalePrintImage=0;PPgs.cx=1;PPgs.cy=1;DocSize.cx=826;DocSize.cy=1169;ShowDetails=0;Orientation=P;Zoom=100;ShowTags=0;OpParams=1;VisibleAttributeDetail=0;ShowOpRetType=1;ShowIcons=1;CollabNums=0;HideProps=0;ShowReqs=0;ShowCons=0;PaperSize=9;HideParents=0;UseAlias=0;HideAtts=0;HideOps=0;HideStereo=0;HideElemStereo=0;ShowTests=0;ShowMaint=0;ConnectorNotation=UML 2.1;ExplicitNavigability=0;ShowShape=1;AllDockable=0;AdvancedElementProps=1;AdvancedFeatureProps=1;AdvancedConnectorProps=1;m_bElementClassifier=1;SPT=1;ShowNotes=0;SuppressBrackets=0;SuppConnectorLabels=0;PrintPageHeadFoot=0;ShowAsList=0;"/>
                        <UML:TaggedValue tag="styleex" value="SaveTag=25BEDCCD;ExcludeRTF=0;DocAll=0;HideQuals=0;AttPkg=1;ShowTests=0;ShowMaint=0;SuppressFOC=0;INT_ARGS=;INT_RET=;INT_ATT=;SeqTopMargin=50;MatrixActive=0;SwimlanesActive=1;KanbanActive=0;MatrixLineWidth=1;MatrixLineClr=0;MatrixLocked=0;TConnectorNotation=UML 2.1;TExplicitNavigability=0;AdvancedElementProps=1;AdvancedFeatureProps=1;AdvancedConnectorProps=1;m_bElementClassifier=1;SPT=1;MDGDgm=;STBLDgm=;ShowNotes=0;VisibleAttributeDetail=0;ShowOpRetType=1;SuppressBrackets=0;SuppConnectorLabels=0;PrintPageHeadFoot=0;ShowAsList=0;SuppressedCompartments=;Theme=:119;"/>
                    </UML:ModelElement.taggedValue>' . "\n";

        $script .= '<UML:Diagram.element>' . "\n";

        $script .= self::getElementPositions($all_objects, $messages, $fragments);

        return $script;
    }

    private static function getElementPositions(Collection $objects, Collection $messages, Collection $fragments): string
    {
        $seqno = 1;
        $script = '';
        $all_objects = $objects->merge($fragments);
        $posMinXFixed =  $all_objects->min('posX');

        if ($posMinXFixed >= 0) {
            $posMinXFixed = $posMinXFixed > 50 ? 0 : 50;
        } else {
            $posMinXFixed = ($posMinXFixed - 50) * (-1);
        }


        /* if ($posMinYFixed <= 0) {
            $posMinYFixed = ($posMinYFixed - 100) * (-1);
        } else {
            if ($posMinYFixed < 100) {
                $posMinYFixed = 100;
            } 
        } */

        /* dump($fragments);
        dump($posMinXFixed);
        dd($posMinYFixed); */
        foreach ($fragments as $fragment) {
            //$posX = ($fragment['posX'] * 0.9) + 10;
            $posX = (($fragment['posX'] + $posMinXFixed) * 0.77);
            $posY = ($fragment['posY'] * 0.77);
            $width = $fragment['width'] * 0.77;
            $height = $fragment['height'] * 0.77;
            $script .= '<UML:DiagramElement geometry="Left=' . $posX . ';Top=' . $posY . ';Right=' . $posX + $width . ';Bottom=' . $height + $posY  . ';" subject="' . $fragment['key'] . '" seqno="' . $seqno . '" style="DUID=' . $fragment['key'] . ';"/>' . "\n";
            $seqno++;
        }

        foreach ($objects as $object) {
            //$posX = $object['posX'] * 0.88;
            $posX = ($object['posX'] + $posMinXFixed - 65) * 0.77;
            $posY = (($object['posY'] - 65) * 0.77);

            $script .= '<UML:DiagramElement geometry="Left=' . $posX . ';Top=' . $posY . ';Right=' . $posX + 100 . ';Bottom=' . $posY + 100 . ';" subject="' . $object['key'] . '" seqno="' . $seqno . '" style="DUID=' . $object['key'] . ';"/>' . "\n";
            $seqno++;
        }

        

        foreach ($messages as $message) {
            $script .= '<UML:DiagramElement geometry="SX=0;SY=0;EX=0;EY=0;Path=;" subject="message' . $message->key . '" style=";Hidden=0;"/>' . "\n";
        }
        $script .= '' . "\n";
        $script .= '</UML:Diagram.element>' . "\n";

        $script .= '</UML:Diagram>' . "\n";
        $script .= '</XMI.content>' . "\n";
        $script .= '<XMI.difference/>' . "\n";
        $script .= '<XMI.extensions xmi.extender="Enterprise Architect 2.5"/>' . "\n";
        $script .= '</XMI>' . "\n";
        //dd($script);
        return $script;
    }

    public static function generateFragment(Collection $fragment, string $diagramName): string
    {
        $script = '';
        $height = $fragment['height'] * 0.877;
        $condition = str_replace(['[', ']'], '', $fragment['text']);
        $condition = str_replace(">", "&gt;", $condition);
        $condition = str_replace("<", "&lt;", $condition);
        $script .= '<UML:Class name="" xmi.id="' . $fragment['key'] . '" visibility="public" namespace="id_package" isRoot="false" isLeaf="false" isAbstract="false">' . "\n";
        $script .= '<UML:ModelElement.taggedValue>' . "\n";
        $script .= '<UML:TaggedValue tag="isSpecification" value="false"/>' . "\n" .
            '<UML:TaggedValue tag="ea_stype" value="InteractionFragment"/>' . "\n";
        $script .= '<UML:TaggedValue tag="ea_ntype" value="';
        $script .= ($fragment['category'] == 'loop') ? 4 : ($fragment['category'] == 'opt' ? 1 : 0);
        $script .= '"/>' . "\n";
        $script .= '<UML:TaggedValue tag="version" value="1.0"/><UML:TaggedValue tag="isActive" value="false"/>' . "\n";
        $script .= '<UML:TaggedValue tag="package" value="id_package"/>' . "\n";
        $script .= '<UML:TaggedValue tag="date_created" value="2023-10-15 21:39:12"/>
                    <UML:TaggedValue tag="date_modified" value="' . date('Y-m-d H:i:s') . '"/>
                    <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                    <UML:TaggedValue tag="tagged" value="0"/>
                    <UML:TaggedValue tag="package_name" value="' . $diagramName . '"/>
                    <UML:TaggedValue tag="phase" value="1.0"/>' . "\n";
        $script .= '<UML:TaggedValue tag="author" value="Usuario"/>
                    <UML:TaggedValue tag="complexity" value="1"/>
                    <UML:TaggedValue tag="status" value="Proposed"/>
                    <UML:TaggedValue tag="styleex" value="ConstructID=0;"/>
                    <UML:TaggedValue tag="tpos" value="0"/>
                    <UML:TaggedValue tag="ea_localid" value="' . $fragment['key'] . '"/>' . "\n";
        $script .= '<UML:TaggedValue tag="ea_eleType" value="element"/>
                    <UML:TaggedValue tag="diagram" value="id_diagram"/>
                    <UML:TaggedValue tag="style" value="BackColor=-1;BorderColor=-1;BorderWidth=-1;FontColor=-1;VSwimLanes=1;HSwimLanes=1;BorderStyle=3;"/>
                    <UML:TaggedValue tag="$ea_xref_property" value="$XREFPROP=$XID={2D610D6F-EC34-42aa-AAAD-535829C1DF53}$XID;$NAM=Partitions$NAM;$TYP=element property$TYP;$VIS=Public$VIS;$PAR=0$PAR;$DES=';
        if (!empty($fragment['text2'])) {
            $condition2 = str_replace(['[', ']'], '', $fragment['text2']);
            $condition2 = str_replace(">", "&gt;", $condition2);
            $condition2 = str_replace("<", "&lt;", $condition2);
            $script .=  "@PAR;Name=$condition2;Size=" . $height / 2 . ";GUID={condition-2};@ENDPAR;";
        }
        $script .=  "@PAR;Name=$condition;Size=" . $height / 2 . ";GUID={condition-1};@ENDPAR;";
        $script .=  '$DES;$CLT={28E06D49-CA1C-4b73-8169-3E87EB7526E2}$CLT;$SUP=&lt;none&gt;$SUP;$ENDXREF;"/>' . "\n";
        $script .= '</UML:ModelElement.taggedValue>' . "\n";
        $script .= '</UML:Class>' . "\n";

        return $script;
    }

    public static function generateMessage($message, int $numberMessage, float $posY): string
    {
        $message = json_decode(json_encode($message), false);
        $script = '';
        $script .= '<UML:Message name="' . $message->text . '" xmi.id="message' . $message->key . '" visibility="public" sender="' . $message->from . '" receiver="' . $message->to . '">' . "\n";
        $script .= '<UML:ModelElement.taggedValue>' . "\n";
        $script .=      '<UML:TaggedValue tag="style" value="1"/>
                            <UML:TaggedValue tag="ea_type" value="Sequence"/>
                            <UML:TaggedValue tag="direction" value="Source -&gt; Destination"/>
                            <UML:TaggedValue tag="linemode" value="1"/>
                            <UML:TaggedValue tag="linecolor" value="-1"/>
                            <UML:TaggedValue tag="linewidth" value="0"/>
                            <UML:TaggedValue tag="seqno" value="' . $numberMessage . '"/>' . "\n";
        $script .=      '<UML:TaggedValue tag="headStyle" value="0"/>
                            <UML:TaggedValue tag="lineStyle" value="0"/>';
        $script .=      '<UML:TaggedValue tag="privatedata1" value="';
        $script .=      $message->synchronous == 'yes' ? 'Synchronous' : 'Asynchronous';
        $script .=     '"/>' . "\n" . '<UML:TaggedValue tag="privatedata2" value="retval=void;"/>
                            <UML:TaggedValue tag="privatedata3" value="Call"/>
                            <UML:TaggedValue tag="privatedata4" value="';
        $script .=      $message->isReturn ? 1 : 0;
        $script .=      '"/>' . "\n" . '<UML:TaggedValue tag="ea_localid" value="' . $message->key . '"/>' . "\n";
        $script .=      '<UML:TaggedValue tag="ea_sourceName" value="Object 1"/>
                            <UML:TaggedValue tag="ea_targetName" value="Object 2"/>';
        $script .=      '<UML:TaggedValue tag="ea_sourceType" value="';
        $script .= strpos($message->from, 'actor') === 0 ? 'Actor' : 'Sequence';
        $script .=      '"/>' . "\n" . '<UML:TaggedValue tag="ea_targetType" value="';
        $script .=  strpos($message->to, 'actor') === 0 ? "Actor" : "Sequence";
        $script .=      '"/>' . "\n" . '<UML:TaggedValue tag="ea_sourceID" value="' . $message->from . '"/>
                            <UML:TaggedValue tag="ea_targetID" value="' . $message->to . '"/>
                            <UML:TaggedValue tag="src_visibility" value="Public"/>
                            <UML:TaggedValue tag="src_isOrdered" value="false"/>
                            <UML:TaggedValue tag="src_targetScope" value="instance"/>
                            <UML:TaggedValue tag="src_changeable" value="none"/>' . "\n";
        $script .=      '<UML:TaggedValue tag="src_isNavigable" value="false"/>
                            <UML:TaggedValue tag="src_containment" value="Unspecified"/>
                            <UML:TaggedValue tag="src_style" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Non-Navigable;"/>
                            <UML:TaggedValue tag="dst_visibility" value="Public"/>
                            <UML:TaggedValue tag="dst_aggregation" value="0"/>
                            <UML:TaggedValue tag="dst_isOrdered" value="false"/>
                            <UML:TaggedValue tag="dst_targetScope" value="instance"/>
                            <UML:TaggedValue tag="dst_changeable" value="none"/>
                            <UML:TaggedValue tag="dst_isNavigable" value="true"/>' . "\n";



        $script .=      '<UML:TaggedValue tag="dst_containment" value="Unspecified"/>
                            <UML:TaggedValue tag="dst_style" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Navigable;"/>
                            <UML:TaggedValue tag="privatedata5" value="SX=0;SY=' . $posY . ';EX=0;EY=-0;$LLB=;LLT=;LMT=;LMB=;LRT=;LRB=;IRHS=;ILHS=;"/>
                            <UML:TaggedValue tag="stateflags" value="Activation=0;"/>
                            <UML:TaggedValue tag="virtualInheritance" value="0"/>
                            <UML:TaggedValue tag="diagram" value="id_diagram"/>
                            <UML:TaggedValue tag="mt" value="' . $message->text . '()"/>' . "\n";
        $script .= '</UML:ModelElement.taggedValue>' . "\n";
        $script .= '</UML:Message>' . "\n";

        return $script;
    }
}
