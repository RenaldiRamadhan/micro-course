<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    // list lesson
    public function index(Request $request)
    {
        $lessons = Lesson::query();

        $chapterId = $request->query('chapter_id');

        $lessons->when($chapterId, function($query) use ($chapterId) {
            return $query->where('chapter_id', '=', $chapterId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $lessons->get()
        ]);
    }

    // untuk mendapatkan detail lesson 

    public function show($id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

      //create api lesson
      public function create(Request $request)
      {
          $rules = [
              'name' => 'required|string',
              'video' => 'required|string',
              'chapter_id' => 'required|integer',
          ];
  
          $data = $request->all();
  
          $validator = Validator::make($data, $rules);
  
          if ($validator->fails()){
              return response()->json([
                  'status' => 'error',
                  'message' => $validator->errors()
              ], 400);
          }
  
          $chapterId = $request->input('chapter_id');
  
          $chapter = Chapter::find($chapterId);
          if (!$chapter) {
              return response()->json([
                  'status' => 'error',
                  'message' => 'chapter not found'
              ], 404);
          }
  
  
          // kalo udah berhasil dan ditemukan di create di database
          $lesson = Lesson::create($data);
          return response()->json([
              'status' => 'success',
              'data' => $lesson
          ]);
      }
        // api update
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string',
            'video' => 'string',
            'chapter_id' => 'integer',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }

        // initu nge cek ada chapter_id nya ga
        $chapterId = $request->input('chapter_id');
        if($chapterId){
            $chapter = Chapter::find($chapterId);
            if (!$chapter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'chapter not found'
                ]);
            }
        }
        // kalo ada ya,update di data body ke database
        $lesson->fill($data);
        $lesson->save();
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

     // delete api lesson
     public function destroy($id)
     {
         $lesson = Lesson::find($id);
 
         if (!$lesson) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'lesson not found'
             ], 404);
         }
 
         $lesson->delete();
         return response()->json([
             'status' => 'success',
             'message' => 'lesson deleted'
         ]);
     }
 }