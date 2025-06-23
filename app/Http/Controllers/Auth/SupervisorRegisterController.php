<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RequestJob;
use App\Models\User; // إضافة استخدام نموذج المستخدم
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail; // لاستخدام خدمة البريد
use Illuminate\Support\Facades\Hash; // لتشفير كلمة المرور
use Illuminate\Support\Str; // لتوليد سلسلة عشوائية لكلمة المرور
use App\Mail\SupervisorCredentialsMail; // استخدام Mailable الخاص ببيانات الاعتماد
use Illuminate\Support\Facades\Log; // لاستخدام سجلات الأخطاء

class SupervisorRegisterController extends Controller
{
    /**
     * عرض نموذج تسجيل المشرف.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // تم إزالة إرسال بريد "Hello" من هنا بناءً على طلبك.
        return view('auth.supervisor-register');
    }

    /**
     * معالجة طلب تسجيل المشرف.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // التحقق من صحة البيانات المدخلة من النموذج
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:request_jobs,email', // البريد الإلكتروني يجب أن يكون فريدًا في جدول طلبات الوظائف
            'cv'         => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // تخزين ملف السيرة الذاتية (CV)
        $cvPath = $request->file('cv')->store('supervisor_cvs', 'public');

        // إنشاء سجل طلب وظيفة جديد
        $requestJob = RequestJob::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'doc_url'    => $cvPath,
            // تم إزالة 'is_accepted' => true لتجنب تشغيل RequestJobObserver
            // إذا كان لا يزال مسجلاً ويستمع للتحديثات
        ]);

        // **************
        // منطق إنشاء المستخدم وإرسال البريد الإلكتروني هنا مباشرةً،
        // بدلاً من RequestJobObserver.
        // **************

        $email = $validated['email'];
        $generatedPassword = Str::random(10); // توليد كلمة مرور مؤقتة

        // التحقق مما إذا كان المستخدم موجودًا بالفعل بهذا البريد الإلكتروني في جدول 'users'
        $user = User::where('email', $email)->first();

        if ($user) {
            // السيناريو 1: المستخدم موجود بالفعل
            Log::info('User with email ' . $email . ' already exists during supervisor registration.');

            // تحديث دور المستخدم إلى مشرف إذا لم يكن كذلك
            if ($user->roleID != 2) { // بافتراض أن 2 هو معرف دور المشرف
                $user->roleID = 2;
                $user->is_approved = true; // هنا يتم تعيين الموافقة الفورية للحساب الحالي
                $user->save();
                Log::info('User role updated to Supervisor for ' . $email);
            }

            // إرسال بريد إلكتروني للمستخدم الحالي (بدون كلمة مرور جديدة)
            // يمكن استخدام Mailable واحد، مع تمرير null لكلمة المرور إذا كان المستخدم موجودًا
            Mail::to($email)->send(new SupervisorCredentialsMail($user, null));
            Log::info('Supervisor status update email sent to existing user: ' . $email);

        } else {
            // السيناريو 2: المستخدم غير موجود، قم بإنشاء مستخدم جديد
            Log::info('No existing user found for ' . $email . '. Creating new supervisor user.');
            try {
                $newUser = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name'  => $validated['last_name'],
                    'email'      => $email,
                    'password'   => Hash::make($generatedPassword), // تشفير كلمة المرور المولدة
                    'roleID'     => 2, // دور المشرف
                    'is_approved'=> true, // هنا يتم تعيين الموافقة الفورية للحساب الجديد
                ]);

                // إرسال البريد الإلكتروني الترحيبي مع بيانات الاعتماد الجديدة
                Mail::to($newUser->email)->send(new SupervisorCredentialsMail($newUser, $generatedPassword));
                Log::info('New supervisor user created and credentials email sent to: ' . $newUser->email);

            } catch (\Illuminate\Database\QueryException $e) {
                // التعامل مع أي أخطاء في قاعدة البيانات أثناء إنشاء المستخدم
                Log::error('Database error while creating supervisor user for ' . $email . ': ' . $e->getMessage());
                // يمكن هنا إضافة منطق لمعالجة الخطأ، مثل إعادة توجيه المستخدم برسالة خطأ
            }
        }

        // إعادة التوجيه إلى صفحة الشكر بعد التسجيل
        return redirect()->route('register.thankyou');
    }
}

