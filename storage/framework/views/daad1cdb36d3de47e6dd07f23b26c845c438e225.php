<?php $__env->startSection('title', 'Tags Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Tags Management</h2>
    <a href="<?php echo e(route('admin.tags.create')); ?>" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span>+</span> Add New Tag
    </a>
</div>

<?php if(session('success')): ?>
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="overflow-hidden bg-white rounded-lg shadow-md">
    <table class="w-full text-left">
        <thead>
            <tr class="text-sm font-semibold text-gray-500 uppercase bg-gray-100">
                <th class="p-4">Tag Name</th>
                <th class="p-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php $__empty_1 = true; $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b border-gray-200 hover:bg-indigo-50">
                    <td class="p-4 font-medium"><?php echo e($tag->name); ?></td>
                    <td class="p-4 text-center space-x-2">
                        <a href="<?php echo e(route('admin.tags.edit', $tag->id)); ?>" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Edit</a>
                        <form action="<?php echo e(route('admin.tags.destroy', $tag->id)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="2" class="p-4 text-center text-gray-500">No tags found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="mt-6">
    <?php echo e($tags->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/tags/index.blade.php ENDPATH**/ ?>