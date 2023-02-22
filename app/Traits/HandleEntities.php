<?php


namespace App\Traits;

trait HandleEntities{
    use HandleSession;
    

    public function index_of_entieits()
    {

    }


    public function create_entities($key)
    {
        $user_session = $this->user_session_data;
        $data = ["command"=>"",];
        $this->add_new_object_to_session("entites",);

        

    }
}