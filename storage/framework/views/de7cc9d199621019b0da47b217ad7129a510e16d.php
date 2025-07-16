<?php $__env->startSection('title', 'Manage Specializations'); ?>

<?php $__env->startSection('content'); ?>
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Specializations</h2>
        <a href="<?php echo e(route('admin.specializations.create')); ?>" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>+</span> Add New Specialization
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
                        <th class="p-3">ID</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">For University</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $specializations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?> 
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800"><?php echo e($specialization->SpecializationID); ?></td>
                            <td class="p-3 text-gray-600"><?php echo e($specialization->name); ?></td>
                            <td class="p-3 text-gray-600"><?php echo e(Str::limit($specialization->description, 50)); ?></td>
                            <td class="p-3 text-gray-600">
                                <?php if($specialization->is_for_university): ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-sm text-center space-x-2">
                                <a href="<?php echo e(route('admin.specializations.show', $specialization->SpecializationID)); ?>" class="px-3 py-1 font-medium text-indigo-600 bg-indigo-100 rounded-md hover:bg-indigo-200">View</a>
                                <a href="<?php echo e(route('admin.specializations.edit', $specialization->SpecializationID)); ?>" class="px-3 py-1 font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200">Edit</a>
                                <form action="<?php echo e(route('admin.specializations.destroy', $specialization->SpecializationID)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this specialization? This action cannot be undone.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="px-3 py-1 font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No specializations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/specializations/index.blade.php ENDPATH**/ ?>