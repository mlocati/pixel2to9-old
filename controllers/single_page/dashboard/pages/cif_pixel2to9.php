<?php

declare(strict_types=1);

namespace Concrete\Package\Pixel2to9\Controller\SinglePage\Dashboard\Pages;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Controller\DashboardPageController;
use DOMDocument;
use DOMXPath;
use LibXMLError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Pixel2to9\ConverterFactory;
use Concrete\Core\Http\ResponseFactoryInterface;

defined('C5_EXECUTE') or die('Access Denied.');

class CifPixel2to9 extends DashboardPageController
{
    /**
     * {@inheritDoc}
     *
     * @see \Concrete\Core\Page\Controller\DashboardPageController::on_start()
     */
    public function on_start(): ?Response
    {
        parent::on_start();
        $this->requireAsset('javascript', 'vue');
        $this->addHeaderItem(
            <<<'EOT'
<style>
[v-cloak], [v-cloak] * {
    display: none;
}
#cp29, #cp29 *, #cp29 * {
    box-sizing: border-box;
}
#cp29 table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
#cp29 tr.cp29-io td {
    vertical-align: top;
    margin: 0;
    padding: 0;
}
#cp29 tr.cp29-io td:first-child {
    padding-right: 10px;
}
#cp29 tr.cp29-io td:last-child {
    padding-left: 10px;
}
#cp29 .cp29-input, #cp29 .cp29-output {
    resize: none;
    width: 100%;
    height: calc(100vh - 330px);
    min-height: 100px;
    white-space: pre;
    font-family: SFMono-Regula, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    border: solid 1px #ddd;
    overflow: auto;
}
#cp29 .alert-danger {
    white-space: pre-wrap;
}
</style>
EOT
        );

        return null;
    }

    public function convert(): JsonResponse
    {
        if (!$this->token->validate('cp29-convert')) {
            throw new UserMessageException($this->token->getErrorMessage());
        }
        $input = $this->request->request->get('cif');
        $doc = $this->loadXML(is_string($input) ? trim($input) : '');
        $xpath = new DOMXPath($doc);
        $converterFactory = $this->app->make(ConverterFactory::class);
        foreach ($converterFactory->getConverters() as $converter) {
            $converter->convert($xpath);
        }
        $doc->formatOutput = true;
        return $this->app->make(ResponseFactoryInterface::class)->json(['xml' => $doc->saveXML()]);
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     */
    private function loadXML(string $xml): DOMDocument
    {
        if ($xml === '') {
            throw new UserMessageException(t('Please specifiy the Concrete CIF to be converted'));
        }
        $oldInternalErrors  = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->preserveWhiteSpace = false;
            if (!$doc->loadXML($xml, LIBXML_BIGLINES)) {
                $errors = libxml_get_errors();
                $lines = [t('Failed to load the XML')];
                if ($errors !== []) {
                    $lines[] = '';
                }
                foreach ($errors as $error) {
                    /** @var \LibXMLError $error */
                    $message = '';
                    if ($error->line) {
                        $message = t('Line %d', $error->line) . ': ';
                    }
                    $message .= trim($error->message);
                    $lines[] = $message;
                }
                throw new UserMessageException(implode("\n", $lines));
            }
            if ($doc->documentElement->tagName !== 'concrete5-cif') {
                throw new UserMessageException(t('It seems the provided XML is not a Concrete CIF file'));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($oldInternalErrors);
        }

        return $doc;
    }
}
