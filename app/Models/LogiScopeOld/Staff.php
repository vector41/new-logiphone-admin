<?php

namespace App\Models\LogiScopeOld;

use App\Libs\Common\ModelClass;
use App\Models\Authority\Authority;
use App\Models\Cars\Car;
use App\Models\OriginalClass;
use App\Scopes\SortIdScope;
use App\Scopes\SortScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Staff extends Model {

    protected $connection = "mysql_old";

    protected $table = 'staff';
    protected $primaryKey = 'id';
}
