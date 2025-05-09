<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Api 
Route::group(['namespace' => 'api', 'prefix' => 'v1'], function () {
    Route::get('/testapi', function () {
        return response()->json([
            'msg' => "thành công"
        ]);
    });
    // Authentication
    Route::post('login', [\App\Http\Controllers\Api\AuthenticationController::class, 'store']);
    Route::post('logout', [\App\Http\Controllers\Api\AuthenticationController::class, 'destroy'])->middleware('auth:api');
    Route::post('register', [\App\Http\Controllers\Api\AuthenticationController::class, 'savenewUser']);
    Route::post('google-sign-in', [\App\Http\Controllers\Api\AuthenticationController::class, 'googleSignIn']);
    Route::post('/password/send-reset-code', [\App\Http\Controllers\Api\PasswordRecoveryController::class, 'sendResetCode']);
    Route::post('/password/reset', [\App\Http\Controllers\Api\PasswordRecoveryController::class, 'resetPassword']);
    
    //UniverInfo
    Route::get('/nganhs', [\App\Http\Controllers\Api\UniverInfoController::class, 'getNganhs']);
    Route::get('/donvi', [\App\Http\Controllers\Api\UniverInfoController::class, 'getDonVis']);
    Route::get('/chuyenNganh', [\App\Http\Controllers\Api\UniverInfoController::class, 'chuyenNganhs']);
    Route::get('/classes', [\App\Http\Controllers\Api\UniverInfoController::class, 'classes']);
    Route::post('/getclasses', [\App\Http\Controllers\Api\UniverInfoController::class, 'getclasses']);

     
    //Profile
    Route::post('updateprofile', [\App\Http\Controllers\Api\ApiUserController::class, 'updateProfile'])->middleware('auth:api');
    Route::get('profile', [\App\Http\Controllers\Api\ApiUserController::class, 'viewProfile'])->middleware('auth:api');
    Route::post('upload-photo', [\App\Http\Controllers\Api\ApiUserController::class, 'uploadPhoto'])->middleware('auth:api');


    //Student
    Route::get('/student/{userId}', [\App\Http\Controllers\Api\StudentController::class, 'show'])->middleware('auth:api');
    Route::put('/student/{userId}', [\App\Http\Controllers\Api\StudentController::class, 'update'])->middleware('auth:api');
    Route::post('student', [\App\Http\Controllers\Api\AuthenticationController::class, 'createStudent'])->middleware('auth:api');

    //Teacher
    Route::get('/teacher/{userId}', [\App\Http\Controllers\Api\TeacherController::class, 'show'])->middleware('auth:api');
    Route::put('/teacher/{userId}', [\App\Http\Controllers\Api\TeacherController::class, 'update'])->middleware('auth:api');
    Route::post('teacher', [\App\Http\Controllers\Api\AuthenticationController::class, 'createTeacher'])->middleware('auth:api');
    
    //Phan cong
    Route::get('/phancong', [\App\Http\Controllers\Api\UniverInfoController::class, 'phancong']);

    //Classes
    Route::get('/getClass', [\App\Http\Controllers\Api\CourseController::class, 'getClassStudents']);
    Route::get('/getStudentCourses', [\App\Http\Controllers\Api\CourseController::class, 'getStudentCourses']);
    
    //attendance
    Route::post('/startAttendance', [\App\Http\Controllers\Api\AttendanceController::class, 'startAttendance']);
    Route::post('/markAttendance', [\App\Http\Controllers\Api\AttendanceController::class, 'markAttendance']);
    Route::post('/closeAttendance', [\App\Http\Controllers\Api\AttendanceController::class, 'closeAttendance']);
    Route::get('/getAttendanceBySchedule', [\App\Http\Controllers\Api\AttendanceController::class, 'getAttendanceBySchedule']);

    //Exercises
    Route::post('/questions', [\App\Http\Controllers\Api\ExerciseController::class, 'storeQuestion']);
    Route::post('/answers', [\App\Http\Controllers\Api\ExerciseController::class, 'storeAnswer']);
    Route::post('/quiz', [\App\Http\Controllers\Api\ExerciseController::class, 'storeQuiz']);
    Route::get('/question-types', [\App\Http\Controllers\Api\ExerciseController::class, 'getQuestionTypes']);
    Route::get('/getQuestions', [\App\Http\Controllers\Api\ExerciseController::class, 'getQuestionsByHocphan']);
    Route::post('/essay-questions', [\App\Http\Controllers\Api\ExerciseController::class, 'storeEssayQuestion']);
    Route::post('/essay-quiz', [\App\Http\Controllers\Api\ExerciseController::class, 'storeEssayQuiz']);
    Route::get('/essay-questions-by-hocphan', [\App\Http\Controllers\Api\ExerciseController::class, 'getEssayQuestionsByHocphan']);
    Route::post('assign-quiz', [\App\Http\Controllers\Api\ExerciseController::class, 'assignQuiz']);
    Route::get('teacher-quizzes', [\App\Http\Controllers\Api\ExerciseController::class, 'getTeacherQuizzes']);
    Route::get('student-assignments', [\App\Http\Controllers\Api\ExerciseController::class, 'getStudentAssignments']);
    Route::get('/trac-nghiem-questions', [\App\Http\Controllers\Api\ExerciseController::class, 'getTracNghiemQuestions']);
    Route::post('/submit-trac-nghiem-quiz', [\App\Http\Controllers\Api\ExerciseController::class, 'submitTracNghiemQuiz']);
    Route::get('/tu-luan-questions', [\App\Http\Controllers\Api\ExerciseController::class, 'getTuLuanQuestions']);
    Route::post('/submit-tu-luan-quiz', [\App\Http\Controllers\Api\ExerciseController::class, 'submitTuLuanQuiz']);
    Route::get('/assignment-submissions/{assignmentId}', [\App\Http\Controllers\Api\ExerciseController::class, 'getAssignmentSubmissions']);
    Route::get('/teacher-assignments', [App\Http\Controllers\Api\ExerciseController::class, 'getTeacherAssignments']);
    Route::delete('/teacher-quiz', [App\Http\Controllers\Api\ExerciseController::class, 'deleteQuiz']);
    Route::post('/delete-assignment', [App\Http\Controllers\Api\ExerciseController::class, 'deleteAssignment']); // Bỏ middleware auth:sanctum
    Route::post('/update-submission-score', [App\Http\Controllers\Api\ExerciseController::class, 'updateSubmissionScore']); 
    Route::get('/hocphan/{hocphanId}/avg-scores', [\App\Http\Controllers\Api\ExerciseController::class, 'getStudentAverageScore']);
    Route::delete('/questions/{id}', [\App\Http\Controllers\Api\ExerciseController::class, 'deleteQuestion']);
    Route::delete('/essay-questions/{id}', [\App\Http\Controllers\Api\ExerciseController::class, 'deleteEssayQuestion']);

    Route::put('/edit-quizzes/{id}',  [\App\Http\Controllers\Api\ExerciseController::class, 'updateQuiz']);
    Route::put('/edit-essay-quizzes/{id}',  [\App\Http\Controllers\Api\ExerciseController::class, 'updateEssayQuiz']);
    Route::get('/show-quizzes/{id}', [\App\Http\Controllers\Api\ExerciseController::class, 'showQuiz']);
    Route::get('/show-essay-quizzes/{id}', [\App\Http\Controllers\Api\ExerciseController::class, 'showEssayQuiz']);

    //Course
    Route::get('/courses', [\App\Http\Controllers\Api\CourseController::class, 'getAvailableCourses']);
    Route::get('/searchCourses', [\App\Http\Controllers\Api\CourseController::class, 'searchCourses']);
    Route::get('/classifyCourses', [\App\Http\Controllers\Api\CourseController::class, 'classifyAvailableCourses']);
    Route::post('/enroll', [\App\Http\Controllers\Api\CourseController::class, 'enrollCourse']);
    Route::post('/getEnroll', [\App\Http\Controllers\Api\CourseController::class, 'getEnrolledCourses']);
    Route::post('/deleteEnroll', [\App\Http\Controllers\Api\CourseController::class, 'deleteEnrollment']);
    Route::get('/timeTable', [\App\Http\Controllers\Api\CourseController::class, 'getTimetable']);
    Route::get('/lichthi', [\App\Http\Controllers\Api\CourseController::class, 'getStudentExamSchedules']);
    Route::get('/lichday', [\App\Http\Controllers\Api\CourseController::class, 'getTeacherSchedule']);
    Route::get('/getListstudentCourse', [\App\Http\Controllers\Api\CourseController::class, 'getStudentsByTeacher']);
    Route::post('/update-enrollment-status', [\App\Http\Controllers\Api\CourseController::class, 'updateEnrollmentStatus']);
    Route::get('/get-student-scores/{studentId}/{hocphanId}', [\App\Http\Controllers\Api\CourseController::class, 'getStudentScores']);
    Route::post('/student-scores/{studentId}/{hocphanId}', [\App\Http\Controllers\Api\CourseController::class, 'updateStudentScores']);
    Route::get('/student-progress/{studentId}', [\App\Http\Controllers\Api\CourseController::class, 'getStudentProgress']);
    Route::get('/teacher-report/{teacherId}', [\App\Http\Controllers\Api\CourseController::class, 'getTeacherReport']);
    
    //Notification
    Route::post('teacher/send-notification', [\App\Http\Controllers\Api\NotificationController::class, 'sendNotification']);
    Route::post('student/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'getStudentNotifications']);

    //Teaching content
    Route::post('send-teaching-content', [\App\Http\Controllers\Api\NotificationController::class, 'uploadTeachingContent']);
    Route::post('get-teaching-content', [\App\Http\Controllers\Api\NotificationController::class, 'getTeachingContentForStudent']);
    Route::post('get-teaching-content-teacher', [\App\Http\Controllers\Api\NotificationController::class, 'getTeachingContentForTeacher']);


    //Khảo sát
    Route::get('/surveys/hocphan/{hocphanId}', [\App\Http\Controllers\Api\SurveyController::class, 'getSurvey']);
    Route::post('/surveys/hocphan/{hocphanId}/submit', [\App\Http\Controllers\Api\SurveyController::class, 'submitSurvey']);
    Route::get('/surveys/hocphan/{hocphanId}/results', [\App\Http\Controllers\Api\SurveyController::class, 'getSurveyResults']);
    Route::get('/surveys/student/{studentId}', [\App\Http\Controllers\Api\SurveyController::class, 'getStudentSurveys']); // API mới
  });