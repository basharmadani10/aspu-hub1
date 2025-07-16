<?php $__env->startSection('title', 'Add New Subject'); ?>

<?php $__env->startSection('content'); ?>
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add New Subject</h2>
        <a href="<?php echo e(route('admin.subjects.index')); ?>" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition duration-150 ease-in-out">
            &larr; Back to Subjects List
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-xl">
        <form action="<?php echo e(route('admin.subjects.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                
                <div>
                    <label for="name" class="block mb-2 text-sm font-semibold text-gray-700">Subject Name</label>
                    <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label for="hour_count" class="block mb-2 text-sm font-semibold text-gray-700">Hour Count</label>
                    <input type="number" name="hour_count" id="hour_count" value="<?php echo e(old('hour_count')); ?>" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    <?php $__errorArgs = ['hour_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="SpecializationID" class="block mb-2 text-sm font-semibold text-gray-700">Specialization</label>
                    <select name="SpecializationID" id="SpecializationID" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                        <option value="">Select a specialization</option>
                        <?php $__currentLoopData = $specializations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($spec->SpecializationID); ?>" <?php echo e(old('SpecializationID') == $spec->SpecializationID ? 'selected' : ''); ?>>
                                <?php echo e($spec->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['SpecializationID'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="prerequisites" class="block mb-2 text-sm font-semibold text-gray-700">Prerequisites (Optional)</label>
                    <select name="prerequisites[]" id="prerequisites" multiple class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent h-48 overflow-y-auto">
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                            <?php if($sub->id !== ($subject->id ?? null)): ?>
                                <option value="<?php echo e($sub->id); ?>" <?php echo e(in_array($sub->id, old('prerequisites', [])) ? 'selected' : ''); ?>>
                                    <?php echo e($sub->name); ?> (<?php echo e($sub->specialization->name ?? 'N/A'); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong class="text-indigo-700">How to select/deselect:</strong>
                        Hold <kbd class="px-1 py-0.5 border rounded bg-gray-100 text-gray-700 text-xs">Ctrl</kbd> (Windows/Linux) or
                        <kbd class="px-1 py-0.5 border rounded bg-gray-100 text-gray-700 text-xs">Cmd</kbd> (Mac) and click to select multiple subjects or to deselect an already selected subject.
                    </p>
                    <?php $__errorArgs = ['prerequisites'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['prerequisites.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="references_text" class="block mb-2 text-sm font-semibold text-gray-700">References (One URL per line, Optional)</label>
                    <textarea name="references_text" id="references_text" rows="6" class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out"><?php echo e(old('references_text')); ?></textarea>
                    <p class="text-sm text-gray-600 mt-2">Enter each reference URL on a new line. The title will be the same as the URL for now.</p>
                    <?php $__errorArgs = ['references_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                

                
                <div class="md:col-span-2">
                    <label for="Description" class="block mb-2 text-sm font-semibold text-gray-700">Description</label>
                    <textarea name="Description" id="Description" rows="4" class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out"><?php echo e(old('Description')); ?></textarea>
                    <?php $__errorArgs = ['Description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="mt-8 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Create Subject
                </button>
            </div>
        </form>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\bm10'\Desktop\ASPU_HUB\resources\views/admin/subjects/create.blade.php ENDPATH**/ ?>