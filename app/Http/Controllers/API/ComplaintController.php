<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Models\Complaint;
use App\Models\Complaint_histories;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\UrlGenerator;
use Exception;

class ComplaintController extends Controller
{

    public function index(Request $request)
    {

        $array_complaint = [];
        // $complaint = Complaint::with('complaint_histories','complaint_histories.user');
        $name = $request->name;
        $officer = $request->officer;
        $url = "";

        if($name){
            if($url=="") $url = "?name=".$name;
            else $url.= "&name=".$name;
        }

        if($officer){
            if($url=="") $url = "?officer=".$officer;
            else $url.= "&officer=".$officer;
        }




        // $complaint = Complaint::with([
        //     'complaint_histories' => function($q){

        //     },
        //     'complaint_histories.user' => function($s) use ($name){
        //         if($name){
        //             $s->where('name','LIKE','%'.$name.'%');
        //         }
        //     }
        // ]);


         $complaint = Complaint::with('complaint_histories','complaint_histories.user')
                    ->whereHas('complaint_histories.user',function($s) use ($name,$officer){
                            if($name || $officer){
                                $s->where('name','LIKE','%'.$name.'%');
                            }

                    })
                    ->whereHas('complaint_histories',function($s) use ($name,$officer){
                            if($name!="" && $officer==""){
                                $s->where('status','draft');
                            }else if($name=="" && $officer!=""){
                                $s->where('status','process');
                            }
                    });


        if($request->date){
            if($url=="") $url = "?date=".$request->date;
            else $url.= "&date=".$request->date;
            $complaint->whereDate('date',$request->date);
        }

        if($request->status){
            if($url=="") $url = "?status=".$request->status;
            else $url.= "&status=".$request->status;
            $complaint->where('status',$request->status);
        }

        // if($request->name){
        //     if($url=="") $url = "?name=".$request->name;
        //     else $url.= "&name=".$request->name;
        //     $complaint->where('name',$request->name);
        // }

        $complaint = $complaint->paginate(10);


        // foreach($complaint as $com){
        //     $histories = Complaint::find($com->id)->history()->get();
        //     $array_histories = [];
        //     foreach($histories as $h){
        //         $user = User::find($h->user_id);
        //         $h->user = $user;
        //         $array_histories[] = $h;
        //     }

        //     $com->histories = $array_histories;
        //     $array_complaint[] = $com;
        // }

        $complaint->withPath(url('api/complaint'.$url));


        return ResponseFormatter::success([
            'complaint' => $complaint
        ],'successfully get data');

    }

    public function store(Request $request)
    {
        // return ResponseFormatter::success($request->user()->id,'Data profile user berhasil diambil');

        try {
        //code...
            $validator = Validator::make($request->all(), [
                'number' => ['required', 'max:50'],
                'title'=>['required'],
                'description'=> ['required'],
                'file'=>['image','max:2048']
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    ['error' => $validator->errors()],
                    'Make sure field isnt null',
                    401
                );
            }

            if ($request->file('file')) {
                $file = $request->file->store('assets/complaint', 'public');
            }else{
                $file = "";
            }

                $complaint = Complaint::create([
                    'number'  => $request->number,
                    'title' => $request->title,
                    'date' => date('Y-m-d H:i:s'),
                    'description' => $request->description,
                    'status'=>'draft',
                    'file'=>$file
                ]);

                $user_id = $request->user()->id;
                $complaint_id = $complaint->id;

                Complaint_histories::create([
                    'user_id' => $user_id,
                    'complaint_id'=>$complaint_id,
                    'status'=>'draft'
                ]);

                $Datacomplaint = Complaint::find($complaint_id);
                $Datacomplainthistories = Complaint::find($complaint_id)->history()->get();

                return ResponseFormatter::success([
                    'complaint' => $Datacomplaint,
                    'complaint_histories' => $Datacomplainthistories
                ], 'Success');

            // $histories =

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }


    }

    public function update(Request $request)
    {
        try {
        //code...
            $validator = Validator::make($request->all(), [
                'id' => ['required'],
                'number' => ['required', 'max:50'],
                'title'=>['required'],
                'description'=> ['required'],
                'file'=>['image','max:2048'],
                'status'=>['in:draft,process,finish,reject'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    ['error' => $validator->errors()],
                    'Make sure field isnt null',
                    401
                );
            }

            $complaintData = Complaint::find($request->id);
            if(!$complaintData){
                return ResponseFormatter::error(
                    ['error' => 'ID cant find'],
                    401
                );
            }



            if ($request->file('file')) {
                Storage::delete($complaintData->file);
                $file = $request->file->store('assets/complaint', 'public');
            }else{
                $file = $complaintData->file;
            }

            if($request->status!=""){
                $status = $request->status;
            }else{
                $status = $complaintData->status;
            }

            $complaint = Complaint::where(['id'=>$request->id])->update([
                'number'  => $request->number,
                'title' => $request->title,
                'description' => $request->description,
                'status'=>$status,
                'file'=>$file
            ]);

            $user_id = $request->user()->id;
            $complaint_id = $complaintData->id;
            if($request->status!=""){
                Complaint_histories::create([
                    'user_id' => $user_id,
                    'complaint_id'=>$complaint_id,
                    'status'=>$request->status
                ]);
            }

            $Datacomplaint = Complaint::find($complaint_id);
            $Datacomplainthistories = Complaint::find($complaint_id)->history()->get();

            return ResponseFormatter::success([
                'complaint' => $Datacomplaint,
                'complaint_histories' => $Datacomplainthistories
            ], 'Success');



        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }


    }


    public function update_status(Request $request)
    {
        try {
        //code...
            $validator = Validator::make($request->all(), [
                'id' => ['required'],
                'status'=>['required','in:draft,process,finish,reject'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    ['error' => $validator->errors()],
                    'Make sure field isnt null',
                    401
                );
            }

            $complaintData = Complaint::find($request->id);
            if(!$complaintData){
                return ResponseFormatter::error(
                    ['error' => 'ID cant find'],
                    401
                );
            }

            $complaint = Complaint::where(['id'=>$request->id])->update([
                'status'=>$request->status,
            ]);


            $user_id = $request->user()->id;
            $complaint_id = $complaintData->id;
            Complaint_histories::create([
                'user_id' => $user_id,
                'complaint_id'=>$complaint_id,
                'status'=>$request->status,
                'information'=>$request->information
            ]);

            $Datacomplaint = Complaint::find($complaint_id);
            $Datacomplainthistories = Complaint::find($complaint_id)->history()->get();

            return ResponseFormatter::success([
                'complaint' => $Datacomplaint,
                'complaint_histories' => $Datacomplainthistories
            ], 'Success');



        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }


    }

    public function delete_batch(Request $request)
    {
        # code...
        try {
            //code...
                $validator = Validator::make($request->all(), [
                    'id' => ['required'],
                ]);

                if ($validator->fails()) {
                    return ResponseFormatter::error(
                        ['error' => $validator->errors()],
                        'Make sure field isnt null',
                        401
                    );
                }


                $idComp = explode(',',$request->id);
                $Allcomplaint = Complaint::whereIn('id',$idComp)->get();
                if(count($Allcomplaint)!=0){
                    foreach($Allcomplaint as $ch){
                        $complaintData = Complaint::find($ch->id);
                        if(!$complaintData){
                            return ResponseFormatter::error(
                                ['error' => 'ID cant find'],
                                401
                            );
                        }
                        Storage::delete($ch->file);
                    }
                }else{
                    return ResponseFormatter::error(
                        ['error' => 'ID cant find'],
                        401
                    );
                }


                $complaint_histories = Complaint_histories::whereIn('complaint_id',$idComp)->delete();
                $complaint = Complaint::whereIn('id',$idComp)->delete();


                return ResponseFormatter::success([
                    'message' => 'Success delete complaint, with ID '.$request->id,
                    'data'=> $Allcomplaint
                ], 'Success');



            } catch (Exception $error) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $error,
                ], 'Authentication Failed', 500);
            }
    }

    public function clear_histories(Request $request)
    {
        # code...
        try {
            //code...
                $validator = Validator::make($request->all(), [
                    'id' => ['required'],
                ]);

                if ($validator->fails()) {
                    return ResponseFormatter::error(
                        ['error' => $validator->errors()],
                        'Make sure field isnt null',
                        401
                    );
                }


                $idComp = explode(',',$request->id);
                $Allcomplaint = Complaint::whereIn('id',$idComp)->get();
                if(count($Allcomplaint)!=0){
                    foreach($Allcomplaint as $ch){
                        $complaintData = Complaint::find($ch->id);
                        if(!$complaintData){
                            return ResponseFormatter::error(
                                ['error' => 'ID cant find'],
                                401
                            );
                        }
                        Storage::delete($ch->file);
                    }
                }else{
                    return ResponseFormatter::error(
                        ['error' => 'ID cant find'],
                        401
                    );
                }


                $complaint_histories = Complaint_histories::whereIn('complaint_id',$idComp)->delete();


                return ResponseFormatter::success([
                    'message' => 'Success delete complaint, with ID '.$request->id,
                    'data'=> $Allcomplaint
                ], 'Success');



            } catch (Exception $error) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $error,
                ], 'Authentication Failed', 500);
            }
    }

    public function delete_histories(Request $request)
    {
        # code...
        try {
            //code...
                $validator = Validator::make($request->all(), [
                    'id' => ['required'],
                ]);

                if ($validator->fails()) {
                    return ResponseFormatter::error(
                        ['error' => $validator->errors()],
                        'Make sure field isnt null',
                        401
                    );
                }


                $idComp = explode(',',$request->id);
                $AllComplaint_histories = Complaint_histories::whereIn('id',$idComp)->get();
                if($AllComplaint_histories){
                    foreach($AllComplaint_histories as $ch){
                        $Complaint_historiesData = Complaint_histories::find($ch->id);
                        if(!$Complaint_historiesData){
                            return ResponseFormatter::error(
                                ['error' => 'ID cant find'],
                                401
                            );
                        }else{
                            Complaint_histories::where('id',$ch->id)->delete();
                        }
                    }
                }else{
                    return ResponseFormatter::error(
                        ['error' => 'ID cant find'],
                        401
                    );
                }




                return ResponseFormatter::success([
                    'message' => 'Success delete complaint, with ID '.$request->id,
                    'data'=> $AllComplaint_histories
                ], 'Success');



            } catch (Exception $error) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $error,
                ], 'Authentication Failed', 500);
            }
    }
}
