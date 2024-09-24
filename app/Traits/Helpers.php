<?php

namespace App\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use DOMDocument;
use DOMXPath;

trait Helpers
{


    /**
     * Get the web page content given the url
     * @param $url - url to get the content from
     * @return $content - content of the url
     */
    public function getWebPageContent($url)
    {

        $process = new Process(['wget', '-qO-', $url]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }


    /**
     * This is a helper function to convert html content into plain text
     * @param $htmlContent - html content to convert
     * @return $plainText - plain text content of the html
     */
    public function convertHtmlToPlainText($htmlContent)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($htmlContent);

        // Remove script and style elements
        while (($script = $dom->getElementsByTagName("script")) && $script->length) {
            $script->item(0)->parentNode->removeChild($script->item(0));
        }
        while (($style = $dom->getElementsByTagName("style")) && $style->length) {
            $style->item(0)->parentNode->removeChild($style->item(0));
        }

        // Use XPath to query only for text nodes that are not within script or style tags
        $xpath = new DOMXPath($dom);
        $textNodes = $xpath->query('//body//text()[not(ancestor::script or ancestor::style)]');

        $plainTextContent = '';
        foreach ($textNodes as $node) {
            $plainTextContent .= trim($node->nodeValue) . ' ';
        }

        return trim($plainTextContent); // Remove leading/trailing spaces
    }
}
