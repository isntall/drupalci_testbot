<?php

/**
 * @file
 * Command class for build.
 */

namespace DrupalCI\Console\Command;

use DrupalCI\Console\Command\DrupalCICommandBase;
use DrupalCI\Console\Helpers\ContainerHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BuildCommand extends DrupalCICommandBase {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('build')
      ->setDescription('Build DrupalCI container image.')
      ->addArgument('container_name', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Docker container image(s) to build.')
    ;
      #->addOption(
      #  'dbtype', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Database types to support', array('mysql')
      #)
      #->addOption('php_version', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'PHP Versions to support', array('5.4'))
      #->addOption('container_type', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Types of container image (db/web) to build.', array('web'))
      #->addOption('container_name', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Names of a specific container image to build.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln("<info>Executing build ...</info>");
    $helper = new ContainerHelper();
    $containers = $helper->getAllContainers();
    $names = $input->getArgument('container_name');
    // TODO: Validate passed arguments
    foreach ($names as $name) {
      if (in_array($name, array_keys($containers))) {
        $output->writeln("<comment>Building <options=bold>$name</options=bold> container</comment>");
        $this->build($name, $input, $output);
      }
      else {
        // Container name not found.  Skip build.
        $output->writeln("<error>No '$name' container found.  Skipping container build.</error>");
        // TODO: Error handling
      }
    }
  }

  /**
   * (#inheritdoc)
   */
  protected function build($name, InputInterface $input, OutputInterface $output) {
    $helper = new ContainerHelper();
    $containers = $helper->getAllContainers();
    $container_path = $containers[$name];
    # Store the current directory to make it trivial to return
    $currentdir = getcwd();
    chdir("./$container_path");
    $output->writeln("-------------------- Start build script --------------------");
    passthru("./build.sh", $return_var);
    $output->writeln("--------------------- End build script ---------------------");
    if ($return_var === 0) {
      $output->writeln("<comment>Container <options=bold>$name</options=bold> build complete.</comment>");
      $output->writeln("<comment>The $name container image should now be available.</comment>");
    }
    else {
      $output->writeln("<error>Build script exited with a non-zero error code: <options=bold>$return_var</options=bold></error>");
      $output->writeln("<comment>Please review the output above to determine the root cause.</comment>");
    }
    # Return us to the original working directory
    chdir($currentdir);
  }
}
