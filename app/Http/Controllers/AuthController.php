<?php

namespace App\Http\Controllers;

use App\Models\DictGoods;
use App\Models\DictLocal;
use App\Models\DictMonster;
use App\Models\Goods;
use App\Models\Player;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function signup(Request $request): array
    {
        $params = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'min:8', 'max:32'],
            'email' => ['required', 'email'],
            'phone_area' => ['required', 'int'],
            'phone_number' => ['required', 'string', 'max:15'],
            'device' => 'required',
        ]);

        $userHas = $this->getUser($params);
        if ($userHas) {
            throw ValidationException::withMessages(['name' => ['用户名/邮箱已存在']]);
        }

        $params['password'] = Hash::make($params['password']);
        $device = $params['device'];
        unset($params['device']);
        $user = User::query()->create($params)->refresh();

        DB::transaction(function () use ($user) {
            $this->service->initPlayer($user->id);
        });
        $user->tokens()->delete();
        return [
            'name' => $user->name,
            'token' => $user->createToken($device)->plainTextToken,
        ];
    }

    private function getUser(array $params)
    {
        return User::query()
            ->where('name', $params['name'])
            ->when($params['email'] ?? false, function ($query) use ($params) {
                $query->orWhere('email', $params['email']);
            })
            ->first();
    }

    public function login(Request $request): array
    {
        $nameType = $request->input('name', '');
        $nameType = strpos($nameType, '@') ? 'email' : 'string';
        $params = $request->validate([
            'name' => ['required', $nameType],
            'password' => ['required', 'min:8', 'max:32'],
            'device' => 'required',
        ]);

        $user = $this->getUser($params);
        if (!$user || !Hash::check($params['password'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['用户名或密码有误'],
            ]);
        }

        DB::transaction(function () use ($user) {
            $player = Player::query()->find($user->id);
            if (!$player) {
                $this->service->initPlayer($user->id);
            }
        });
        $user->tokens()->delete();
        return [
            'name' => $user->name,
            'token' => $user->createToken($params['device'])->plainTextToken,
        ];
    }

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
    }

    public function userInfo(): array
    {
        if (Auth::guest()) {
            abort(401);
        }

        return $this->service->getAllInfo();
    }

    public function systemDict()
    {
        return [
            'errors' => [
                'userNotHas' => '账号不存在',
                'authReject' => '账号或密码有误',
                'hpLacking' => '健康过低',
                'moneyLacking' => '缺钱',
                'goodsLacking' => '货品不足',
                'unequipped' => '尚未装备',
                'equipped' => '已被装备',
                'workBusy' => '已在工作中',
                'goodsTypeFailed' => '道具类型有误',
                'adventureNotHas' => '未知的冒险目标',
                'adventureNotFound' => '冒险已经结束',
                'mustInFight' => '需要战斗型冒险',
            ],
            'goods' => DictGoods::query()->get(),
            'monster' => DictMonster::query()->get(),
            'local' => DictLocal::query()->get(),
        ];
    }
}
