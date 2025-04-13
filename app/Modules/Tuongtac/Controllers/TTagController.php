<?php
namespace App\Modules\Tuongtac\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TTagItem;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TTagController extends Controller
{
    public function store_item_tag($item_id,$tag_ids,$item_code)
    {
        $datatag = array();
        if(!$tag_ids || count($tag_ids) == 0)
            return;
        foreach($tag_ids as $tag_id)
        {
            
            $tag = TTag::where('id', $tag_id)->orWhere('title',$tag_id)->first();
            if(!$tag)
            {
                $datatag['title'] = $tag_id;
                $slug = Str::slug( $datatag['title'] );
                $slug_count = TTag::where('slug',$slug)->count();
                if($slug_count > 0)
                {
                    $slug .= time() ;
                }
                $datatag['slug'] = $slug;
                
                $tag = TTag::create($datatag);
                sleep(1);
            }
            $data['tag_id'] = $tag->id;
            $data['item_id'] = $item_id;
            $data['item_code'] = $item_code;
            TTagItem::create($data);
            $tag->hit += 1;
            $tag->save();
        }
    }
    public function update_item_tag($item_id,$tag_ids,$item_code)
    {
        $sql = "delete from t_tag_items where item_code='".$item_code."' and item_id = ".$item_id;
        \DB::select($sql);
         $this->store_item_tag($item_id,$tag_ids,$item_code);
    }
    
}