<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Communitie; // تأكد من استيراد نموذج Communitie
use App\Models\Community_Manager; // تأكد من استيراد نموذج Community_Manager
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    /**
     * Display the admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        // التحقق من صحة بيانات الدخول
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // البحث عن المستخدم باستخدام البريد الإلكتروني
        $user = User::where('email', $credentials['email'])->first();

        // التحقق من وجود المستخدم
        if (!$user) {
            // استخدام رسائل الأخطاء القياسية بدلاً من dd() في الإنتاج
            return back()->withErrors([
                'email' => 'البريد الإلكتروني غير موجود.',
            ]);
        }

        // التحقق من صحة كلمة المرور
        if (!Hash::check($credentials['password'], $user->password)) {
            // استخدام رسائل الأخطاء القياسية بدلاً من dd()
            return back()->withErrors([
                'password' => 'كلمة المرور غير صحيحة.',
            ]);
        }

        // التحقق من صلاحية الدور (roleID = 2 للمشرف)
        // هذا الشرط يتم التحقق منه قبل محاولة المصادقة لضمان أن المستخدم هو مشرف بالفعل
        if ($user->roleID != 2) {
            // تسجيل الخروج إذا كان المستخدم ليس مشرفًا، لتجنب أي جلسة غير مرغوب فيها
            // وتقديم رسالة خطأ مناسبة.
            Auth::logout();
            return back()->withErrors([
                'email' => 'هذا الحساب ليس لديه صلاحيات المشرف.',
            ]);
        }

        // محاولة تسجيل الدخول
        if (Auth::attempt($credentials)) {
            // تجديد الجلسة لمنع هجمات تثبيت الجلسة
            $request->session()->regenerate();

            // *** المنطق المحسّن هنا: ربط المشرف بجميع المجتمعات عند الحاجة فقط ***
            // جلب جميع المجتمعات
            $communities = Communitie::all();

            // جلب المجتمعات التي يديرها المستخدم حاليًا (كائنات Community_Manager)
            // استخدام eager loading لعلاقة managedCommunities لتحسين الأداء
            // (تأكد أن علاقة managedCommunities معرفة في نموذج User بشكل صحيح)
            $user->load('managedCommunities');
            $userManagedCommunityIds = $user->managedCommunities->pluck('community_id')->toArray();

            $needsUpdate = false;
            // التحقق مما إذا كان عدد المجتمعات التي يديرها المشرف أقل من إجمالي المجتمعات
            // أو إذا كانت هناك مجتمعات جديدة لم يتم ربطها بعد.
            if (count($userManagedCommunityIds) < $communities->count()) {
                $needsUpdate = true;
            } else {

                foreach ($communities as $community) {
                    if (!in_array($community->id, $userManagedCommunityIds)) {
                        $needsUpdate = true;
                        break;
                    }
                }
            }



            if ($needsUpdate) {
                foreach ($communities as $community) {

                    if (!in_array($community->id, $userManagedCommunityIds)) {
                        Community_Manager::create([
                            'user_id'      => Auth::user()->id,    // ID المشرف الذي سجل الدخول
                            'community_id' => $community->id,      // ID المجتمع
                            'is_active'    => true,                // يمكن تعديل هذا بناءً على متطلباتك
                        ]);
                    }
                }
            }

            return redirect()->intended(route('admin.dashboard'));
        }


        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
