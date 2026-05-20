<?php

namespace App\Services;

use ZipArchive;
use SimpleXMLElement;
use Exception;

class PptxParser
{
    /**
     * Parse a PPTX file and extract its slides structured with titles and text content.
     *
     * @param string $filePath Absolute path or relative path to the PPTX file.
     * @return array Array of slides, or empty array if failed/unsupported.
     */
    public static function parse($filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        try {
            $presentationXml = $zip->getFromName('ppt/presentation.xml');
            $presentationRels = $zip->getFromName('ppt/_rels/presentation.xml.rels');

            if (!$presentationXml || !$presentationRels) {
                $zip->close();
                return [];
            }

            $xmlPresentation = new SimpleXMLElement($presentationXml);
            $xmlRels = new SimpleXMLElement($presentationRels);

            // Build relationship map
            $rels = [];
            foreach ($xmlRels->Relationship as $rel) {
                $rels[(string)$rel['Id']] = (string)$rel['Target'];
            }

            // Extract slides in order
            $slideFiles = [];
            $xmlPresentation->registerXPathNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
            $xmlPresentation->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

            $sldIds = $xmlPresentation->xpath('//p:sldId');
            foreach ($sldIds as $sldId) {
                $rId = (string)$sldId->attributes('r', true)->id;
                if (isset($rels[$rId])) {
                    $target = $rels[$rId];
                    if (strpos($target, 'ppt/') !== 0) {
                        $target = 'ppt/' . $target;
                    }
                    $slideFiles[] = $target;
                }
            }

            $slides = [];
            foreach ($slideFiles as $idx => $slidePath) {
                $slideXml = $zip->getFromName($slidePath);
                if (!$slideXml) {
                    continue;
                }

                $xmlSlide = new SimpleXMLElement($slideXml);
                $xmlSlide->registerXPathNamespace('p', 'http://schemas.openxmlformats.org/presentationml/2006/main');
                $xmlSlide->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

                $shapes = $xmlSlide->xpath('//p:sp');
                
                $slideTitle = '';
                $slideSubtitle = '';
                $slideContent = [];
                $slideType = 'definition'; // default type

                foreach ($shapes as $shape) {
                    $phType = '';
                    $phs = $shape->xpath('.//p:ph');
                    if (!empty($phs)) {
                        $phType = (string)$phs[0]['type'];
                    }

                    $paras = $shape->xpath('.//a:p');
                    $shapeTextLines = [];
                    foreach ($paras as $para) {
                        $textRuns = $para->xpath('.//a:t');
                        $line = '';
                        foreach ($textRuns as $run) {
                            $line .= (string)$run;
                        }
                        $line = trim($line);
                        if ($line !== '') {
                            $isBullet = false;
                            if ($para->xpath('.//a:pPr') && $para->xpath('.//a:pPr[@lvl]')) {
                                $isBullet = true;
                            }
                            $shapeTextLines[] = [
                                'text' => $line,
                                'is_bullet' => $isBullet,
                            ];
                        }
                    }

                    if (empty($shapeTextLines)) {
                        continue;
                    }

                    if ($phType === 'title' || $phType === 'ctrTitle') {
                        $slideTitle = implode(' ', array_column($shapeTextLines, 'text'));
                    } else if ($phType === 'subTitle') {
                        $slideSubtitle = implode(' ', array_column($shapeTextLines, 'text'));
                    } else {
                        foreach ($shapeTextLines as $lineInfo) {
                            $slideContent[] = [
                                'text' => $lineInfo['text'],
                                'is_bullet' => $lineInfo['is_bullet']
                            ];
                        }
                    }
                }

                // Determine slide type
                if ($idx === 0) {
                    $slideType = 'title';
                } else {
                    $hasBullets = false;
                    foreach ($slideContent as $item) {
                        if ($item['is_bullet']) {
                            $hasBullets = true;
                            break;
                        }
                    }
                    $slideType = $hasBullets ? 'list' : 'definition';
                }

                $slides[] = [
                    'number' => $idx + 1,
                    'title' => $slideTitle ?: ($idx === 0 ? 'Welcome' : 'Slide ' . ($idx + 1)),
                    'subtitle' => $slideSubtitle,
                    'content' => $slideContent,
                    'type' => $slideType
                ];
            }

            $zip->close();
            return $slides;

        } catch (Exception $e) {
            $zip->close();
            return [];
        }
    }
}
