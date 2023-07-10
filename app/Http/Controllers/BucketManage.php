<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Ball;
use App\Models\Bucket;
use App\Models\BucketSuggestion;
use Illuminate\Support\Facades\DB;


class BucketManage extends Controller
{
   
    function index() {
        $data = Ball::get();
        return view('bucket_form',['ballData' => $data]);
    }

    function store(Request $request){
        
        $inputs = $request -> input();
        unset($inputs['_token']);
        $res = ['status'=>'error','msg' => "Something Wrong",'data'=>[]];

        if($inputs['ftype'] == 'fb'){

            unset($inputs['ftype']);
            $inputs['remain_volume'] = $inputs['volume'];
            $inputs['bucket_name'] = Str::title($inputs['bucket_name']);
            $existCheck = Bucket::where("bucket_name", $inputs['bucket_name'])->first();
            // check data exist or not
            if($existCheck){
                $bucket_data = $existCheck->update($inputs);
                BucketSuggestion::truncate();
                Bucket::query()->update(['remain_volume' => DB::raw('volume'),'bucket_store_status' => 'not_started']);
                $res = ['status'=>'success','msg' => "Bucket Updated",'data'=>$bucket_data];
                
            }else{
                $bucket_data = Bucket::create($inputs);
                BucketSuggestion::truncate();
                Bucket::query()->update(['remain_volume' => DB::raw('volume'),'bucket_store_status' => 'not_started']);
                $res = ['status'=>'success','msg' => "Bucket Added",'data'=>$bucket_data];
            }
            
        }elseif($inputs['ftype'] == 'bucketSuggest'){
                $checkBuc = Bucket::get();
                if($checkBuc->count() == 0){
                    $res = ['status'=>'success','msg' => "No bucket founded. Please add minimume 1 bucket.",'data'=>[],'actype' => "ballToBuckS" ];
                }else{
                    return $this->manageBucketVolume($request->input());
                }
                
        }else{

            unset($inputs['ftype']);
            $inputs['ball_name'] = Str::title($inputs['ball_name']);
            $existCheck = Ball::where("ball_name", $inputs['ball_name'])->first();
            // check data exist or not
            if($existCheck){
                $ball_data = $existCheck->update($inputs);
                BucketSuggestion::truncate();
                Bucket::query()->update(['remain_volume' => DB::raw('volume'),'bucket_store_status' => 'not_started']);
                $res = ['status'=>'success','msg' => "Ball Updated",'data'=>$existCheck->first(),'actype' => "update"];
            }else{
                BucketSuggestion::truncate();
                Bucket::query()->update(['remain_volume' => DB::raw('volume'),'bucket_store_status' => 'not_started']);
                $ball_data = Ball::create($inputs);
                $res = ['status'=>'success','msg' => "Ball Added",'data'=>$ball_data,'actype' => "add" ] ;
            }
        }

        return response()->json($res);
    }

    function manageBucketVolume($inputs){
        
        if(count($inputs) > 0){
            $bucketFull = "false";
            $blIds = $inputs['ball_id'];
            $blTotal = $inputs['total_ball'];
            $blSizes = $inputs['ball_size'];
            $blName = $inputs['ball_nm'];
            $BallCountStop = -1;
            $remainBallArray = [];
            for ($i=0; $i < count($blIds); $i++) { 
                //ids
                for ($j=1; $j <= $blTotal[$i] ; $j++) { 
                   $checkVa = $this->checkBucket($blSizes[$i]);
                   if(!empty($checkVa)){
                        BucketSuggestion::create(['ball_id' => $blIds[$i],'bucket_id' => $checkVa['id']]);
                        $rDeff = $checkVa->remain_volume - $blSizes[$i];
                        $checkVa->update(['remain_volume' => $rDeff,'bucket_store_status' => 'in_proccess']);
                    }else{
                        $BallCountStop = $j-1;
                        $remainBallArray[$blName[$i]] = $blTotal[$i] - $BallCountStop;
                        $bucketFull = "true";
                        break;
                   }
                }
                
            }
            
            
            //"1 Pink Ball and 2 Blue balls cannot be accommodated in any bucket since there is no available space."
            // reset proccess for next new cycle
            Bucket::query()->where(['remain_volume' => 0])->update(['bucket_store_status' => 'not_started']);
                
            if($bucketFull == "true"){
                    $k=1;
                    $txt="";
                    foreach ($remainBallArray as $key => $value) {
                        if($k == 1){
                            $txt .= "$value $key Ball ";
                        }else{  
                            $txt .= "And $value $key Ball ";
                        }
    
                        $k++;
                        # code...
                    }
                    $txt .= " cannot be accommodated in any bucket since there is no available space.";
                    
                $res = ['status'=>'success','msg' => $txt,'data'=>[],'actype' => "ballToBuckE",'remain' => $remainBallArray ] ;
            }else{
                $res = ['status'=>'success','msg' => "Ball added in bucket",'data'=>[],'actype' => "ballToBuckS" ] ;
            }
            return response()->json($res);
            //dd($inputs);
            
        }
        

    }

    function checkBucket($blSizes){
        $res = Bucket::where("remain_volume", ">=", $blSizes)->where('bucket_store_status','in_proccess')->orderBy('remain_volume','DESC')->first();
        if(empty($res)){
            $res = Bucket::where("remain_volume", ">=", $blSizes)->orderBy('remain_volume','DESC')->first();
        }
        return $res;
        
    }

    function result() {
       
        $res =BucketSuggestion::join('buckets as A', 'A.id', '=', 'bucket_suggestions.bucket_id')
        ->join('balls as C', 'C.id', '=', 'bucket_suggestions.ball_id')
        ->selectRaw('COUNT(*) AS TOTAL,A.bucket_name,C.ball_name')
        ->groupBy('A.bucket_name','C.ball_name')
        ->get();
        if(!empty($res)){
            $result = [];
            $sent = "";
            foreach ($res as $value) {
                $result[$value['bucket_name']][] = [
                    "TOTAL" => $value['TOTAL'],
                    "ball_name" => $value['ball_name']
                ];
            }
            $groupedData = array();
            foreach ($res as $value) {
                $groupedData[$value['bucket_name']][] = $value;
            }

            foreach ($groupedData as $key => $value) {
                $sent .="<li>Bucket " . $key . ": ";
                foreach ($value as $v) {
                    $sent .= $v['TOTAL'] . " " . $v['ball_name'] . " ";
                }
                $sent .= "</li>";
            }
            $res = ['status'=>'success','msg' => "result available",'data'=>$sent,'actype' => "found" ] ;
        }else{
            $res = ['status'=>'success','msg' => "No result available",'data'=>"",'actype' => "noData" ] ;
        }

        
        return response()->json($res);
    }



    
}
