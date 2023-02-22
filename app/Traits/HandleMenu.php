<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait HandleMenu {
    use SendMessage;

    public function menu_index()
    {
        if($this->menu_item_id == "main_menu:1")
        {
            $this->send_interactive_menu("swp_procedures");
            die;
        }
        if($this->menu_item_id == "swp_procedures:4")
        {
            $text = <<<MSG
            Definition: Confined space is
            a space with limited entry and
            egress and not suitable for
            human inhabitants. An
            example is the interior of a
            storage tank, occasionally
            entered by maintenance
            workers but not intended for
            human occupancy
            MSG;
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
            // $dock_link = asset("docs/SWP on Confined Space Entry rev.1.pdf");
            $dock_link = "https://naijaprojecthub.com/ali_bot/public/docs/SWP%20on%20Confined%20Space%20Entry%20rev.1.pdf";
            $this->send_post_curl($this->make_document_message($this->userphone,$dock_link,"SWP on Confined Space Entry rev"));
            die;
        }
        if($this->menu_item_id == "main_menu:3")
        {
            $text = <<<MSG
            Visitor Induction Program hasbeen developed from HSE
            Group to ensure our Visitors a
            Safe & Secure Visit to our
            Facilities.
            MSG;
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
            $text ="https://kipickw.sharepoint.com/:v:/r/sites/KIPIC2/Groups%20Document%20Library/HSE%20Group/Document/Visitor%20Induction/SIV_AR.mp4?csf=1&web=1&e=2fW0ry";
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
            die;
        } 
        if($this->menu_item_id == "main_menu:7")
        {
            $this->send_interactive_menu("report_incident");
            die;
        }

        if($this->menu_item_id == "report_incident:1")
        {
            $text = <<<MSG
            Please fill below details in
            order to report your incident:
            -Incident Description:
            -Incident Location:
            -Other Information:
            -Send pictures, if any
            MSG;
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
            $this->IncidentReport();
            $this->continue_session_step();

            die;

        }

        if($this->menu_item_id == "main_menu:8")
        {
            $text = <<<MSG
            Welcome to Ask HSE, how can I help
            you ?
            MSG;
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
        }else{
            $text = <<<MSG
            Sorry I don't understand the command.... on the bright still I'm still developing and will soon understand you!
            MSG;
            $data = $this->make_text_message($text);
            $this->send_post_curl($data);
            die;
        }


        
     
    }




    public function determin_menu()
    {
        
    }

    public function get_command_and_value_menu()
    {
        $data = explode(":",$this->menu_item_id);
        return $data;
    }
    
}