<?php

namespace App\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use DOMDocument;
use DOMXPath;

trait Helpers
{

    /**
     * Get the content from a given URL
     * @param string $url
     * @return string
     */
    public function readWebPageFromUrl($url)
    {
        // try calling the getWebPageContent method
        try {
            $content = $this->getWebPageContent($url);
        } catch (\Throwable $th) {
            $content = 'ERROR: The page cannot be found' . $th->getMessage();
        }

        // return the plain text version of the page
        return $this->convertHtmlToPlainText($content);
    }


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

        $trimmedString = trim($plainTextContent, ' \n\r\t\v\0'); // Remove leading/trailing spaces
        $cleaned = preg_replace('/[\r\n]+/', '', $trimmedString);

        //dd($cleaned);
        return $cleaned;
    }
}
