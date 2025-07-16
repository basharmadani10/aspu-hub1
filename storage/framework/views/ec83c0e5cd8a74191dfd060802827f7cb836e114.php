<?php $__env->startSection('title', 'Add New Tags'); ?>

<?php $__env->startSection('content'); ?>
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add New Tags</h2>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-md">
        <form action="<?php echo e(route('admin.tags.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Subject Name -->
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Tags Name</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>






            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-indigo-600 rounded-lg md:w-auto hover:bg-indigo-700">Create Tags</button>
            </div>
        </form>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/tags/create.blade.php ENDPATH**/ ?>