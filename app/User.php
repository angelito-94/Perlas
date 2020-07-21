<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nombre', 'telefono', 'email', 'password', 'tipuser',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function User($id){
        return User::where('users.id','=',$id)->get();
    }

    public static function Rol($id){
        return User::where('users.id','=',$id)->get();
    }

    public static function Usuarios(){
        return User::where('users.tipuser','=','USUARIO')->get();
    }
}
