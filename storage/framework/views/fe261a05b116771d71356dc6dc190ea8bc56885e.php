<?php $__env->startSection('title', 'Subject Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Subject Management</h2>
    <a href="<?php echo e(route('admin.subjects.create')); ?>" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span>+</span> Add New Subject
    </a>
</div>


<?php if(session('success')): ?>
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="space-y-8">
    
    <?php $__empty_1 = true; $__currentLoopData = $specializations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="overflow-hidden bg-white rounded-lg shadow-md">
            <div class="p-5 bg-gray-50 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-700"><?php echo e($specialization->name); ?></h3>
            </div>

            
            <?php if($specialization->subjects->isNotEmpty()): ?>
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-sm font-semibold text-gray-500 uppercase bg-gray-100">
                            <th class="p-4">Subject Name</th>
                            <th class="p-4">Hour Count</th>
                            <th class="p-4">Prerequisites</th> 
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php $__currentLoopData = $specialization->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b border-gray-200 hover:bg-indigo-50">
                                <td class="p-4 font-medium"><?php echo e($subject->name); ?></td>
                                <td class="p-4"><?php echo e($subject->hour_count); ?></td>
                                <td class="p-4">
                                    <?php $__empty_2 = true; $__currentLoopData = $subject->requiredPrerequisites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prerequisite): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2"><?php echo e($prerequisite->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        <span class="text-gray-500">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center space-x-2">
                                    <a href="<?php echo e(route('admin.subjects.edit', $subject->id)); ?>" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Edit</a>
                                    <form action="<?php echo e(route('admin.subjects.destroy', $subject->id)); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                
                <div class="p-4 text-center text-gray-500">
                    No subjects have been added to this specialization yet.
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        
        <div class="p-10 text-center bg-white rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-700">No Specializations Found</h3>
            <p class="mt-2 text-gray-500">Please add specializations first before assigning subjects to them.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/subjects/index.blade.php ENDPATH**/ ?>