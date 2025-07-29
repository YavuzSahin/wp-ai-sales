<?php
class agents{
    public static function listAgents(){
        $db = database::connect();
        return $db->table('agents')
            ->where('status', 1)
            ->orderBy('id', 'ASC')
            ->getAll();
    }
    public static function getAgent($id){

        if(!empty($id)) {
            $db = database::connect();
            $agent = $db->table('agents')->where('id', $id)->get();
            if (count((array)$agent) <= 0) {
                $agent = new ArrayObject();
                @$agent->name = '<span class="text-danger"><i class="fal fa-user-times"></i> No Agent Set</span>';
            }
        }else{
            $agent = new ArrayObject();
            @$agent->name = '<span class="text-danger"><i class="fal fa-user-times"></i> No Agent Set</span>';
        }
        return $agent;
    }
}
