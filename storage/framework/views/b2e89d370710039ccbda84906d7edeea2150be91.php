<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPU HUB - Supervisor Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen py-12">
        <div class="w-full max-w-lg p-8 space-y-6 bg-white rounded-2xl shadow-lg">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Apply to be a Supervisor</h1>
                <p class="mt-2 text-sm text-gray-600">Your application will be reviewed by an administrator.</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="px-4 py-3 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                    <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('supervisor.register.store')); ?>" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block mb-2 text-sm font-medium text-gray-700">First Name</label>
                        <input id="first_name" type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" required autofocus class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="last_name" class="block mb-2 text-sm font-medium text-gray-700">Last Name</label>
                        <input id="last_name" type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                 <div>
                    <label for="cv" class="block mb-2 text-sm font-medium text-gray-700">Upload CV (PDF, DOCX)</label>
                    <input id="cv" type="file" name="cv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/auth/supervisor-register.blade.php ENDPATH**/ ?>