<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



trait SendMessage
{
    use MessagesType;



    public function send_greetings_message()
    {

        $text = <<<MSG
        Welcome to Ask HSE Chatbot. Please send your Email & ID for verification.
        MSG;
        $this->makeUserLogin();
        $this->send_post_curl($this->make_text_message($text));
        $this->continue_session_step();
    }









    public function send_text_message($text, $to = "")
    {
        if ($to == "") {
            $to = $this->userphone;
        }
        $this->send_post_curl($this->make_text_message($to, $text));
        return response("", 200);
    }

    public function send_media_message($type, $file_url, $caption = null)
    {
        switch ($type) {
            case 'video':
                $this->send_post_curl($this->make_video_message($this->userphone, $file_url, $caption));
                break;
            case 'image':
                $this->send_post_curl($this->make_image_message($this->userphone, $file_url, $caption));
                break;
            case 'document':
                $this->send_post_curl($this->make_document_message($this->userphone, $file_url, $caption));
                break;
            default:
                $this->send_text_message("An Error Occured with the media file! support will be notified");
                die;
                break;
        }

        return true;
    }



    public function send_post_curl($post_data)
    {
        $token = env("WB_TOKEN");
        $url = env("WB_MESSAGE_URL");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ),
        ));

        $response = curl_exec($curl);
        echo $response;

        // curl_close($curl);

    }
    public function send_get_curl_wa_media($media_id = "", $url = "")
    {
        $token = env("WB_TOKEN");
        if ($url != "") {
            $url = $url;
        } else {
            $url = env("WB_MEDIA_URL");
            $url = str_replace("[MEDIA_ID]", $media_id, $url);
        }


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$token}"
            ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        echo curl_error($curl);
        return $response;
    }


    // public function download_image($url, $fileName)
    // {


    //     $bearerToken = env("WB_TOKEN");
    //     $options = array(
    //         'http' => array(
    //             'header' => "Authorization: Bearer $bearerToken\r\n"
    //         )
    //     );
    //     $context = stream_context_create($options);
    //     $img = file_get_contents($url, false, $context);
    //     $publicPath = public_path($fileName);
    //     echo '<img src="data:image/jpeg;base64,' . base64_encode($img) . '"/>';
    //     // die;
    //     file_put_contents($publicPath, $img);
    // }

    public function download_image($url,$ext)
    {
       
        $token = env("WB_TOKEN");
       
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST , "GET");
        curl_setopt($ch,CURLOPT_ENCODING , "");
    
        $headers    = [];
        $headers[]  = "Authorization: Bearer " . $token;
        $headers[]  = "Accept-Language:en-US,en;q=0.5";
        $headers[]  = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $raw = curl_exec($ch);
        
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

      return $raw;
    }
}
