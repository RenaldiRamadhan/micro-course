<?php

namespace App\Http\Controllers;


use App\Models\Review;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
     //create api lesson
     public function create(Request $request)
     {
         $rules = [
             'user_id' => 'required|integer',
             'course_id' => 'required|integer',
             'rating' => 'required|integer|min:1|max:5',
             'note' => 'string',
         ];
 
         $data = $request->all();
 
         $validator = Validator::make($data, $rules);
 
         if ($validator->fails()){
             return response()->json([
                 'status' => 'error',
                 'message' => $validator->errors()
             ], 400);
         }
 
         $courseId = $request->input('course_id');
 
         $course = Course::find($courseId);
         if (!$course) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'course not found'
             ], 404);
         }

          // cek user id akses ke service user
        $userId =  $request->input('user_id');
        $user = getUser($userId);

        if ($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

         // pengecekan untuk mencegah duplikasi data
         $isExistReview = Review::where('course_id', '=', $courseId)
         ->where('user_id', '=',  $userId)
         ->exists();
         
            if ($isExistReview) {
            return response()->json([
            'status' => 'error',
            'message' => 'review already taken'
            ], 409);
        }
 
         // kalo udah berhasil dan ditemukan di create di database
         $review = Review::create($data);
         return response()->json([
             'status' => 'success',
             'data' => $review
         ]);
     }

     // api update
    public function update(Request $request, $id)
    {
        $rules = [
            'rating' => 'integer|min:1|max:5',
            'note' => 'string'
        ];

        $data = $request->except('user_id', 'course_id');

        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $review = Review::find($id);
        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }

        // kalo ada ya,update di data body ke database
        $review->fill($data);
        $review->save();
        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }
    // delete api review
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }

        $review->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'review deleted'
        ]);
    }
}
 