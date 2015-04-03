<?php namespace Fragale\Helpers;
/* Clase para el manejo de argumentos en los CRUDS */

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Fragale\Helpers\PathsInfo;
use Collective\Html\FormFacade as Form;

class CrudsArgs 
{
    public $Master;
    public $master_id;
    public $master_record_col2_template;
    public $detail_records_col2_definitions;
    public $master_record_template;
    public $models;
    
    public function __construct($models)
    {

        $p=new PathsInfo();

        $this->models=$models;

        $this->setSessionVars();

        /*template para la 2da columna*/        
        $template='/'.$this->models.'/customs/detail_records_col2';
        $filename=app_path().'/views'.$template.'.blade.php'; 
        if (!file_exists($filename)){
            $template='';
        }
        $this->detail_records_col2_definitions = $template; // template para los registros detalle si existen

        $master     = Input::get('master');
        $master_id  = Input::get('master_id');

        $models='0';

        $this->Master=ucwords(trim($master));       
        $this->master_id=$master_id;

        if(class_exists($this->Master)){
            $Master=$this->Master;
            $models=$Master::MODELS;                    
        }

        /*template para el master record*/
        $template="/$models/customs/master_record";
        $filename=$p->pathViews().$template.'.blade.php';
        //dd($filename);
        if (!file_exists($filename)){
            $template='';
        }
        $this->master_record_template = $template;   // template para el master record  

        /*template para la 2da columna*/
        $template="/$models/customs/master_record_col2";
        $filename=app_path().'/views'.$template.'.blade.php';
        if (!file_exists($filename)){
            $template='';
        }
        $this->master_record_col2_template = $template; // template para el master record si corresponde    
    }


    function showArgs($id){     
        return array($id, 'master' => $this->Master, 'master_id' => $this->master_id );
    }

    function editArgs($id){     
        return $this->showArgs($id);
    }

    function moveArgs($id,$direction){      
        $show=$this->showArgs($id);
        $move=array('move' => $direction);
        return array_merge ($show,$move);
    }

    function basicArgs($master='',$master_id=''){       
        if($master==''){
            $master     =$this->Master;
            $master_id  =$this->master_id;
        }
        return array('master' => $master, 'master_id' => $master_id );
    }

    function inputsMaster()
    {
        return "<input name=\"master\" type=\"hidden\" value=\"".$this->Master."\">"."<input name=\"master_id\" type=\"hidden\" value=\"".$this->master_id."\">";;
    }

    function stringAddMaster($link)
    {
        return $this->doStringLink($link,$this->Master,$this->master_id);
    }

    function doStringLink($link,$master_model,$master_id)
    {
        if (str_contains($link,'?')){
            $prefijo='&';
        } else {
            $prefijo='?';
        }

        $link = $link.$prefijo.'master='.$master_model.'&master_id='.$master_id;
        return $link;
    }

    function setSessionVars()
    {
        /*establece su propia URI request*/
        $models=$this->models;
        //echo($models.'.request_uri'.' '.$_SERVER['REQUEST_URI']);

        Session::put($models.'.request_uri', $_SERVER['REQUEST_URI']);
        return true;
    }

    function getMasterRequestURI()
    {
        /*si tiene un master record entonces determina la URI del master (a la que tiene que retornar desde un detail) 
        si no tiene un master record devulve false */
        if(class_exists($this->Master)){
            $Master=$this->Master;
            $models=$Master::MODELS;                    
            return Session::get($models.'.request_uri', '');
        }
        return false;
    }

    function getMasterName()
    {
        if(class_exists($this->Master)){
            $Master=$this->Master;
            $models=$Master::MODELS;                    
            return trans('forms.backTo').' '.trans('forms.'.$models);
        }
        return false;
    }   

    function getMasterField($field)
    {
        $Master=$this->Master;
        $record=$Master::find($this->master_id);
        eval("\$value=\$record->$field;");
        return $value;
    }       

    function doTitle($title,$size='1')
    {
                $title ="<h$size>$title</h$size>";
        return $title;
    }           

    /*to be removed-------------------------------------------------------------------------------------------------*/
    function sortArgs($field,$order){       
        return array('sort' => $field, 'order' => $order, 'master' => $this->Master, 'master_id' => $this->master_id );
    }



function toolBar($record){ 

    /*links botones*/
    $routeBtnDelete ='#DeleteModal';
    $btnGoBack      =link_to_route($this->models.'.index', trans('forms.goBack'), $this->basicArgs(), array('class' => 'btn btn-success'));

    $currentKeyId=$record->id;
    $nextKeyId=$record->getNextId($currentKeyId);
    $prevKeyId=$record->getPreviousId($currentKeyId);
    $firstKeyId=$record->getIdMaxOrMin('min');
    $lastKeyId=$record->getIdMaxOrMin('max');
    $classBtnDelete     ='';

    $classD1=$classD2=$classD3=$classD4='';

    if ($currentKeyId==$firstKeyId){$classD1='disabled';}
    if ($currentKeyId==$prevKeyId){$classD2='disabled'; }
    if ($currentKeyId==$nextKeyId){$classD3='disabled'; }
    if ($currentKeyId==$lastKeyId){$classD4='disabled'; }

/*
    $linkL0=link_to_route($this->models.'.index', '', $this->basicArgs(), array('class' => 'btn btn-info glyphicon glyphicon-list-alt '));
    $linkL1=link_to_route($this->models.'.create', '', $this->basicArgs(), array('class' => 'btn btn-info glyphicon glyphicon-plus '));
    $linkL2=link_to_route($this->models.'.edit', '', $this->editArgs($currentKeyId), array('class' => 'btn btn-info glyphicon glyphicon-edit '));
    $linkL3=link_to_route($this->models.'.edit', '', $this->editArgs($currentKeyId), array('class' => 'btn btn-info  glyphicon glyphicon-duplicate disabled'));
    $linkL4=Form::open(array('route' => array($route, $rid), 'method' => 'delete'));
    $linkL4=$linkL4."<button type=\"submit\" class=\"btn btn-danger glyphicon glyphicon-trash\" onclick=\"".$confirmation."\" title=\"Delete this Item\" ></button>";
    $linkL4=$linkL4.Form::close();
*/


    $linkL0=$this->toolButton('index',$record->id);
    $linkL1=$this->toolButton('create',$record->id);
    $linkL2=$this->toolButton('edit',$record->id);
    $linkL3=$this->toolButton('copy',$record->id,'disabled');
    $linkL4=$this->toolButton('destroy',$record->id);

    $linkD1=link_to_route($this->models.'.show', '', $this->showArgs($firstKeyId), array('class' => 'btn btn-info glyphicon glyphicon-step-backward '.$classD1));
    $linkD2=link_to_route($this->models.'.show', '', $this->showArgs($prevKeyId), array('class' => 'btn btn-info glyphicon glyphicon-chevron-left '.$classD2));
    $linkD3=link_to_route($this->models.'.show', '', $this->showArgs($nextKeyId), array('class' => 'btn btn-info glyphicon glyphicon-chevron-right '.$classD3));
    $linkD4=link_to_route($this->models.'.show', '', $this->showArgs($lastKeyId), array('class' => 'btn btn-info glyphicon glyphicon-step-forward '.$classD4));
    
    $toolbar =<<<EOT
    <div class="row" align="right">  
        <div class="col-md-6">
            <div class="btn-group">         
                $linkL0
            </div>
        </div>                 
        <div class="col-md-6">
            <div class="btn-group">
                $linkD1
                $linkD2
                $linkD3
                $linkD4
            </div>
            <div class="btn-group">
                $linkL1
                $linkL2
                $linkL3
            </div>
            <div class="btn-group">         
                $linkL4
            </div>
        </div>                 
    </div> 
EOT;

return $toolbar;
}

function toolButton($action,$id,$disabled=''){ 
    $route=$this->models.'.'.$action;
    switch ($action) {
        case 'index':
            $html=link_to_route($route, '', $this->basicArgs(), array('class' => 'btn btn-info glyphicon glyphicon-list-alt '.$disabled));
            break;
        case 'create':
            $html=link_to_route($route, '', $this->basicArgs(), array('class' => 'btn btn-info glyphicon glyphicon-plus '.$disabled));
            break;
        case 'edit':        
            $html=link_to_route($route, '', $this->editArgs($id), array('class' => 'btn btn-info glyphicon glyphicon-edit '.$disabled));
            break;
        case 'copy':        
            $html=link_to_route($this->models.'.edit', '', $this->editArgs($id), array('class' => 'btn btn-info  glyphicon glyphicon-duplicate '.$disabled));
            break;            
        case 'show':
            $html=link_to_route($route, '', $$this->showArgs($id), array('class' => 'btn btn-info glyphicon glyphicon-step-backward '.$disabled));
            break;
        case 'destroy':
            $confirmation="if(!confirm('".trans('forms.AreSureToDelete')."?')){return false;};";
            $html=Form::open(array('route' => array($route, $id), 'method' => 'delete'));
            $html=$html."<button type=\"submit\" class=\"btn btn-danger glyphicon glyphicon-trash\" onclick=\"".$confirmation."\" title=\"Delete this Item\" ></button>";
            $html=$html.Form::close();
            break;
        default:
            $html='';
            break;
    }
    return $html;

}

}
