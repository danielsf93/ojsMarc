<?php
/**
 * @file ojs-3.4.0-4/plugins/importexport/ojsMarc/OjsMarcPlugin.inc.php
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
        // Export the selected submission
        case 'export':
            $selectedSubmissionId = (int) $request->getUserVar('selectedSubmissionId');
            $submission = Repo::submission()->get($selectedSubmissionId);
            if ($submission && $submission->getStatus() === Submission::STATUS_PUBLISHED) {
                header('content-type: text/comma-separated-values');
                header('content-disposition: attachment; filename=ojs' . '.txt');

                $this->export(LazyCollection::make([$submission]), 'php://output');
            } else {
                // Handle case when submission is not found or not published
                // Redirect or display an error message
            }
            break;

        // Display the list of submissions for export
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

    public function export(LazyCollection $submissions, $filename)
{
    $fp = fopen($filename, 'wt');

    foreach ($submissions as $submission) {
        $dados = $this->dados($submission);
        fwrite($fp, $dados . PHP_EOL); // Adiciona quebra de linha ao final de cada entrada
    }

    fclose($fp);
}
    
public function dados($submission)
{
    $teste = "ronaldo";
    $keywords = $submission->getCurrentPublication()->getLocalizedData('keywords');
    $keywordsString = is_array($keywords) ? implode(', ', $keywords) : ''; // Converter array em string

    // Palavras fixas e quebras de linha
    $fixedWords = [
        'ID: ' . $submission->getId(),
        'Título: ' . $submission->getCurrentPublication()->getLocalizedFullTitle(),
    ];

    // Adiciona keywords se houver
    if ($keywordsString !== '') {
        $fixedWords[] = 'Palavras chave: ' . $keywordsString;
    } else {
        $fixedWords[] = 'Palavras chave: N/A';
    }

    // Palavras fixas restantes com quebras de linha
    $fixedWords[] = 'Meu textoa: texto01';
    $fixedWords[] = 'Meu textob: texto02';
    $fixedWords[] = 'Meu textoc: ' . $teste;

    return implode(PHP_EOL, $fixedWords); // Retorna array de strings
}



    
    
    
}
