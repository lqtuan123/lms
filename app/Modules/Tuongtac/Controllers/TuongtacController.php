<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TTagItem;
use  App\Modules\Tuongtac\Models\TMotion;
use  App\Modules\Tuongtac\Models\TMotionItem;
use  App\Modules\Tuongtac\Models\TRecommend;
use  App\Modules\Tuongtac\Models\TVoteItem;
use Illuminate\Support\Str;
class TuongtacController extends Controller
{
    public function index()
    {
        return view('Tuongtac::frontend.index');
    }
    public static function getSctiptActionBar()
    {
        return view('Tuongtac::frontend.actionbar.script')->render();
    }

    public function getVotes($itemId)
    {
       
    }

    public static function getActionBar($item_id, $item_code )
    {
        
        if ($userId = auth()->id()){
            $mark = \DB::select('select count(id) as tong from t_recommends where user_id='.$userId.' and item_id ='.$item_id.' and item_code="'.$item_code.'"');
            if($mark[0] ->tong == 0){
                $data['isBookmarked'] = 0;
            }
            else{
                $data['isBookmarked'] = 1;
            }
        }else{
            $data['isBookmarked'] = 0;
        }

        
        // $comments = Tcomment::where('item_id',$item_id)->where('item_code',$item_code)
        //     ->where('status','active')->where('parent_id',0)->get();
        $data['item_id'] = $item_id;
        $data['item_code'] = $item_code;
        $data['isBookmarked'] = TRecommend::hasBookmarked($item_id,$item_code);
        $recommends =  \DB::select('select count(id) as tong from t_recommends where item_id ='.$item_id.' and item_code="'.$item_code.'"');
        
        if($recommends[0] ->tong == 0)
            $data['tong'] = null;
        else
            $data['tong'] = $recommends[0]->tong;

        $data['reactions'] = TMotion::all();
        $motion = TMotionItem::where('item_id',$item_id)->where('item_code',$item_code)->first();
      
        if( $motion)
        {
            $data['rcount'] = 0;
            foreach ($motion->motions as $key =>$mcount)
            {
                $data['rcount']+= $mcount;
            }
            // dd( $data['rcount']);
            $reactionCount = $motion->motions;
            foreach ($reactionCount as $key=> $mcount)
            {
                foreach($data['reactions'] as $reaction)
                {
                    if($reaction->title == $key)
                    {
                        $reaction->mcount = $mcount;
                      
                    }
                }
            }
        }   
        $data['voteRecord'] = DB::table('t_vote_items')->where('item_id', $item_id)->first();

         
        $comments =  \DB::select('select count(id) as tong from t_comments where item_id ='.$item_id.' and item_code="'.$item_code.'"');
        if($comments[0]->tong == 0)
            $data['hasComment'] = 0;
        else
            $data['hasComment'] = $comments[0]->tong;

        $html = view('Tuongtac::frontend.actionbar.show',$data)->render();
        return $html;
    }
}