<?php $__env->startSection('content'); ?>
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">My Supervised Communities</h2>
        
        <a href="<?php echo e(route('admin.communities.create')); ?>" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>+</span> Add New Community
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md">
        <div class="p-4">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
                        <th class="p-3">Community Name</th>
                        <th class="p-3">Total Subscribers</th>
                        <th class="p-3">Student Subscribers</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $communities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $community): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?php echo e($community->name); ?></td>
                            <td class="p-3 text-gray-600"><?php echo e($community->total_subscribers); ?></td>
                            <td class="p-3 text-gray-600"><?php echo e($community->student_subscribers); ?></td>
                            <td class="p-3 text-sm text-center space-x-2"> 
                                <a href="<?php echo e(route('admin.communities.show', $community->id)); ?>" class="px-3 py-1 font-medium text-indigo-600 bg-indigo-100 rounded-md hover:bg-indigo-200">Manage</a>
                                <a href="<?php echo e(route('admin.communities.edit', $community->id)); ?>" class="px-3 py-1 font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200">Edit</a>
                                <form action="<?php echo e(route('admin.communities.destroy', $community->id)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this community? This action cannot be undone.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="px-3 py-1 font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">You are not supervising any communities yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/communities/index.blade.php ENDPATH**/ ?>