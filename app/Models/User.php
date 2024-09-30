<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    //use EntrustUserTrait;
    //use SoftDeletes;

    use EntrustUserTrait { EntrustUserTrait::restore as private restoreA; }
    use SoftDeletes { EntrustUserTrait::restore as private restoreB; }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','github_id','auth_type','profile_photo','is_profile_completed','is_blocked','github_user_name','type','api_token','instructor_id','type'
    ];

    protected $visible = [
                'id','name','email','mobile','profile_photo','address'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

    public function newToken(){
        return Str::random(60);
    }

    public function getProfilePhotoAttribute($name){
        if(file_exists(public_path().'/uploads/profilePhoto/'.$name)){
            return [
                'name'=>$name,
                'path'=>asset('uploads/profilePhoto/'.$name),
            ];
        }else{
            return [
                'name'=>'avatar.png',
                'path'=>asset('uploads/profilePhoto/avatar.png'),
            ];
        }
    }

       public function user_information()
    {
        return $this->hasOne(InstructorInfo::class);
    }
    public function spasilization()
    {
        return $this->hasMany(UserSpecialization::class);
    }

    public function learningSequences()
    {
        return $this->belongsToMany(LearningSequence::class, 'learning_sequence_pedagogies')
            ->withPivot('pedagogy_tag_id')
            ->withTimestamps();
    }

    public function pedagogyTags()
    {
        return $this->belongsToMany(PedagogyTag::class, 'learning_sequence_pedagogies')
            ->withPivot('learning_sequence_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(User::class, 'instructor_id');
    }


    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students', 'student_id', 'course_id');
    }


    public function instructorCourses()
    {
        return $this->belongsToMany(Course::class, 'course_students', 'instructor_id', 'course_id');
    }


    public function learningSequence()
    {
        return $this->belongsToMany(LearningSequence::class, 'course_learning_sequences')
            ->withPivot('course_id', 'title', 'description', 'content_type', 'order_column')
            ->withTimestamps();
    }


    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

}
