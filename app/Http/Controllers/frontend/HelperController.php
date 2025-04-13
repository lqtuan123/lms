<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Agency;
class HelperController extends Controller
{
    protected $s3;
    protected $title;
    protected $domain;
    protected $image_name;
    public function __construct( )
    {
        $this->s3 = "itcctv.s3.";
        $this->title = "";
        $this->domain = "";
        $this->image_name = "";
    }
    public function addActivity(  $title)
    {
        
        // session()->push('latest_activity','....' .$title);
        $data = array();
        $user_name ="một người dùng ";
        if(auth()->user())
        {
            $data['user_id'] = auth()->user()->id;
            $user_name =auth()->user()->full_name;
        }
        else
        {
            $data['user_id'] = 0;
        }
        $data['ip']= Request::ip();
        $data['title'] =$user_name.' '. $title;
       
        session()->forget('latest_activity');
        session()->put('latest_activity', $data['title']);
        \App\Models\Logactivity::create($data);
    }
    public function uploadImageInContent($content  ,$domain = "")
    {
        $this->domain = $domain;
        $pattern = '/<img[^>]+src="([^"]+)"/';
        $modified_html = preg_replace_callback($pattern, function($matches) {
            // Perform upload action for each image
            $substring = $this->s3;
            $url = $matches[1];
            // echo '<br/><br/><br/><br/><br/>--'.$matches[1];
            if (strpos($url, "http") === false) 
            {
                $url = 'https:'.$url;
            }
            if (strpos($url, $substring) !== false || strpos($url,'itcctv.vn') !== false) 
            {
                return $matches[0];
            }
            else
            {
                
                $fileController = new \App\Http\Controllers\Frontend\FilesFrontendController();
                try{
                    
                    $uploadedImagePath = $fileController->blogimageUpload($url);
                    // echo '<br/>--upload'.$uploadedImagePath ;
                }
                catch(e)
                {
                    throw new \Exception("File not found");
                }
                // Replace original src attribute with uploaded image link
                return str_replace($matches[1], $uploadedImagePath, $matches[0]);
            }
        
        }, $content);

        return  $modified_html;
    }
}