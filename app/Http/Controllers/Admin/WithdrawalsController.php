<?php

namespace App\Http\Controllers\Admin;

use App\classes\UpLoad;
use App\Model\Payment;
use App\Model\Withdrawals;
use App\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class WithdrawalsController extends Controller
{

    public function pay(Request $request)
    {


        $store_path = '/images/payments/';

        $image = UpLoad::create('image')
            ->request($request)
            ->target('main_image')
            ->store_path($store_path)
            ->watermark_path('watermark_logo.png')
            ->position('center-center')
//            ->resizePercentage(80)
//            ->resize_percent(75)
            ->makeUpload();

        DB::beginTransaction();

        try {
            Withdrawals::where('id', $request->withdrawal_id)->update(
                [
                    'is_pay' => 1,
                    'image_path' => $image['image_path'][0],

                    'description' => $request->description,

                ]
            );
            Payment::create([
                'user_id' => $request->user_id,
                'price' => -$request->price,
                'payment_type' => 3,
                'description' => "واریز به حساب ",
            ]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return back()->with('error', 'خطا در ثبت اطلاعات ');

        }

        $text = $request->description;
        $text .= "\n";
        $text .= Verta::now();
        $text .= "\n";
        $text .= url('');


        $user = User::select('chat_id')->find($request->user_id);
        if ($user->chat_id > 0) {
            sendMessageToBot($text, $user->chat_id);
        }

        sendMessageToBot($text, admin_bot_id());

        return back()->with('success', 'اطلاعات با موفقیت ثبت شد');

    }

    public function list(Request $request)
    {
        $condition = false;
        $search = Input::get('search', '');
        $is_pay = Input::get('is_pay', 2);
        if ($is_pay < 2) {
            $condition = true;
        }
         $withdrawals = Withdrawals::with(['user' => function ($q) use ($search) {
            return $q->SearchByKeyword($search)->with(['payments' => function ($q) {
                return $q->select('user_id', DB::raw('ifnull(sum(price),0) as price'))->groupBy('user_id');
            }])
                ->with(['visited_links' => function ($q) {


                    $q->select('id', 'visited_id')
                        ->selectRaw(DB::raw('sum(price) as price'))
                        ->selectRaw(DB::raw('count(price) as click'))->groupBy('visited_id');
                }])
                ->with(['visited_link' => function ($q) {


                    $q->select('id', 'visited_id', 'created_at as my_created_at')
                        ->orderBy('id', 'DESC');
                }])
                ->select('id', 'fname', 'lname', 'email', 'shaba_number', 'card_number', 'referer_id', 'mobile', 'created_at')
                ->withCount('withdrawals');


        }])
            ->whereHas('user', function ($q) use ($search) {
                return $q->SearchByKeyword($search)->with(['payments' => function ($q) {
                    return $q->select('user_id', DB::raw('ifnull(sum(price),0) as price'))->groupBy('user_id');
                }]);
            })
            ->withCount('referrers')
            ->orderBy('id', 'DESC');

        if ($condition) {
            $withdrawals = $withdrawals->where('is_pay', $is_pay);
        }
          $withdrawals = $withdrawals->paginate(30);


        if ($request->ajax()) {
            try {
                return view('layouts.material.admin.withdrawals.table', compact('withdrawals'))->render();
            } catch (\Throwable $e) {
            }
        }
        return view('layouts.material.admin.withdrawals.list', compact('withdrawals'));
    }
}
