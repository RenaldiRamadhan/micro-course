<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\MyCourse;
use App\Models\Review;
use App\Models\Mentor;
use App\Models\Lessons;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    // get list coruses (paginate mean mau nampilin berapa course dalam 1 halaman)
    public function index(Request $request)
    {
        $courses = Course::query();

        // fillter search by keyword
        $q = $request->query('q');
        $status = $request->query('status');

        $courses->when($q, function($query) use ($q) {
            return $query->whereRaw("name LIKE '%".strtolower($q)."%'");
        });

        // fillter search by status

        $courses->when($status, function($query)use($status){
            return $query->where('status', '=', $status);
        });


        return response()->json([
            'status' => 'success',
            'data' => $courses->paginate(10)
        ]);
    }

    // untuk mendapatkan detail create 

    public function show($id)
    {
        $course = Course::with('chapters.lessons')
        ->with('mentor')
        ->with('images')
        ->find($id);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        
        // menampilkan review user
        $reviews = Review::where('course_id', '=', $id)->get()->toArray();

        // menampilkan data dari review pada course dengan memanggil dari service user
        if (count($reviews)>0){
            $userIds = array_column($reviews, 'user_id');
            $users = getUserByIds($userIds);

            // kalo eror nampilin data review kosong
            if ($users['status'] === 'error') {
                $reviews = [];
                // kalo service user ga mati ngambil data dari service user dengan id nya dan service course dari review dengan review id nya
            } else {
                foreach($reviews as $key => $review) {
                    $userIndex = array_search($review['user_id'], array_column($users['data'], 'id'));
                    $reviews[$key] ['users'] = $users['data'] [$userIndex];
                }
            }
        }

        // menghitung jumlah murid yang join kelas
        $totalStudent = MyCourse::where('course_id', '=', $id)->count();

        // menampilkan video lessons pada course
        $totalVideos = Chapter::where('course_id', '=', $id)->withCount('lessons')->get()->toArray();
        // nampilin total videos dalam chapter yang diitung dari video per lesson.(per lesson 1 vide)
        $finalTotalVideos = array_sum (array_column($totalVideos, 'lessons_count'));


        // menampilkan hasil review user
        $course['reviews'] = $reviews;
        // menampilkan hasil total student
        $course['total_student'] = $totalStudent;
        // menampilkan hasil total videos dalam 1 chapter = berapa lessons juga
        $course['totalVideos'] = $finalTotalVideos;

        // return ke frontend
        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    //create api course
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'certificate' => 'required|boolean',
            'thumbnail' => 'string|url',
            'type' => 'required|in:free,premium',
            'status' => 'required|in:draft,published',
            'price' => 'integer',
            'level' => 'required|in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'required|integer',
            'description' => 'string'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $mentorId = $request->input('mentor_id');

        $mentor = Mentor::find($mentorId);
        if (!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ], 404);
        }


        // kalo udah berhasil dan ditemukan di create di database
        $course = Course::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    // update api course
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string',
            'certificate' => 'boolean',
            'thumbnail' => 'string|url',
            'type' => 'in:free,premium',
            'status' => 'in:draft,published',
            'price' => 'integer',
            'level' => 'in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'integer',
            'description' => 'string'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // check api course nya ada ga
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        // check apakah mentor id ada di database
        $mentorId = $request->input('mentor_id');
        if ($mentorId) {
            $mentor = Mentor::find($mentorId);
            if (!$mentor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'mentor not found'
                ], 404);
            }
        }

        // update kalo diatas ga ada eror dan masuk ke db
        $course->fill($data);
        $course->save();

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    // delete api course
    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        $course->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'course deleted'
        ]);
    }
}
