<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @var $testFrameworkDir string - Must be defined in parent script.
 */

/** Copy test modules to app/code/Magento to make them visible for Magento instance */
$pathToCommittedTestModules = $testFrameworkDir . '/../_files/Magento';
$pathToInstalledMagentoInstanceModules = $testFrameworkDir . '/../../../../app/code/Magento';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pathToCommittedTestModules, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
);
/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isDir()) {
        $source = $file->getPathname();
        $relativePath = substr($source, strlen($pathToCommittedTestModules));
        $destination = $pathToInstalledMagentoInstanceModules . $relativePath;
        $targetDir = dirname($destination);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        copy($source, $destination);
    }
}
unset($iterator, $file);

// Register the modules under '_files/'
$pathPattern = $pathToInstalledMagentoInstanceModules . '/TestModule*/registration.php';
$files = glob($pathPattern, GLOB_NOSORT);
if ($files === false) {
    throw new \RuntimeException('glob() returned error while searching in \'' . $pathPattern . '\'');
}
foreach ($files as $file) {
    include $file;
}

register_shutdown_function('deleteTestModules', $pathToCommittedTestModules, $pathToInstalledMagentoInstanceModules);

/**
 * Delete all test module directories which have been created before
 *
 * @param string $pathToCommittedTestModules
 * @param string $pathToInstalledMagentoInstanceModules
 */
function deleteTestModules($pathToCommittedTestModules, $pathToInstalledMagentoInstanceModules)
{
    $filesystem = new \Symfony\Component\Filesystem\Filesystem();
    $iterator = new DirectoryIterator($pathToCommittedTestModules);
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isDir() && !in_array($file->getFilename(), ['.', '..'])) {
            $targetDirPath = $pathToInstalledMagentoInstanceModules . '/' . $file->getFilename();
            $filesystem->remove($targetDirPath);
        }
    }
    unset($iterator, $file);
}