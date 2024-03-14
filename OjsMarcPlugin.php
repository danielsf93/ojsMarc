<?php
/**
 * @file ojsMarc.inc.php
 */

namespace APP\plugins\importexport\ojsMarc;

use APP\core\Application;
use APP\facades\Repo;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Illuminate\Support\LazyCollection;
use PKP\plugins\ImportExportPlugin;

class OjsMarcPlugin extends ImportExportPlugin
{
    public function register($category, $path, $mainContextId = NULL)
    {
        $success = parent::register($category, $path);

        $this->addLocaleData();

        return $success;
    }

    public function getName()
    {
        return 'ojsMarc';
    }

    public function getDisplayName()
    {
        return __('plugins.importexport.ojsMarc.name');
    }

    public function getDescription()
    {
        return __('plugins.importexport.ojsMarc.description');
    }

    public function display($args, $request)
    {
        parent::display($args, $request);

        // Get the journal, press or preprint server id
        $contextId = Application::get()->getRequest()->getContext()->getId();

        // Use the path to determine which action
        // should be taken.
        $path = array_shift($args);
        switch ($path) {

            // Stream a CSV file for download
            case 'exportAll':
                header('content-type: text/comma-separated-values');
                header('content-disposition: attachment; filename=articles-' . date('Ymd') . '.txt');

                $submissions = $this->getAll($contextId);

                $this->export($submissions, 'php://output');

                break;

            // When no path is requested, display a list of submissions
            // to export and a button to run the `exportAll` path.
            default:
                $templateMgr = TemplateManager::getManager($request);

                $templateMgr->assign([
                    'pageTitle' => __('plugins.importexport.ojsMarc.name'),
                    'submissions' => $this->getAll($contextId),
                ]);

                $templateMgr->display(
                    $this->getTemplateResource('export.tpl')
                );
        }
    }

    public function executeCLI($scriptName, &$args)
    {
        $csvFile = array_shift($args);
        $contextId = array_shift($args);

        if (!$csvFile || !$contextId) {
            $this->usage('');
        }

        $submissions = $this->getAll($contextId);

        $this->export($submissions, $csvFile);
    }

    public function usage($scriptName)
    {
        echo __('plugins.importexport.ojsMarc.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

    /**
     * A helper method to get all published submissions for export
     *
     * @param int contextId Which journal, press or preprint server to get submissions for
     */
    public function getAll(int $contextId): LazyCollection
    {
        return Repo::submission()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();
    }

    /**
     * A helper method to stream all published submissions
     * to a CSV file
     */
    public function export(LazyCollection $submissions, $filename)
    {
        $fp = fopen($filename, 'wt');
        fputcsv($fp, ['ID', 'Title']);

        /** @var Submission $submission */
        foreach ($submissions as $submission) {
            fputcsv(
                $fp,
                [
                    $submission->getId(),
                    $submission->getCurrentPublication()->getLocalizedFullTitle()
                ]
            );
        }

        fclose($fp);
    }
}
