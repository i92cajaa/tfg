<?php


namespace App\Shared\Utils;

use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;


class PdfCreator
{

    const TEMP_DIR = '/var/www/html/var/temp';

    private Mpdf $mpdf;

    public function __construct(
    )
    {
        $this->mpdf = new Mpdf(['orientation' => 'P', 'format' => 'A4', 'tempDir' => self::TEMP_DIR]);
    }
    public function resetInstance(array $config = ['orientation' => 'P', 'format' => 'A4'])
    {
        $this->mpdf = new Mpdf($config);
    }
    public function addFontDirectory(string $directory)
    {
        $this->mpdf->AddFontDirectory($directory);
    }
    public function setDisplayMode(string $displayMode)
    {
        $this->mpdf->SetDisplayMode($displayMode);
    }
    public function addPage(
        $orientation = '',
        $condition = '',
        $resetpagenum = '',
        $pagenumstyle = '',
        $suppress = '',
        $mgl = '',
        $mgr = '',
        $mgt = '',
        $mgb = '',
        $mgh = '',
        $mgf = '',
        $ohname = '',
        $ehname = '',
        $ofname = '',
        $efname = '',
        $ohvalue = 0,
        $ehvalue = 0,
        $ofvalue = 0,
        $efvalue = 0,
        $pagesel = '',
        $newformat = ''
    )
    {
        $this->mpdf->AddPage(
            $orientation,
            $condition,
            $resetpagenum,
            $pagenumstyle,
            $suppress,
            $mgl,
            $mgr,
            $mgt,
            $mgb,
            $mgh,
            $mgf,
            $ohname,
            $ehname,
            $ofname,
            $efname,
            $ohvalue,
            $ehvalue,
            $ofvalue,
            $efvalue,
            $pagesel,
            $newformat
        );
    }
    public function setHeader(string $html)
    {
        $this->mpdf->defaultheaderline = 0;
        $this->mpdf->SetHeader($html,null, true);

    }
    public function setFooter(string $html)
    {
        $this->mpdf->defaultfooterline = 0;
        $this->mpdf->SetFooter($html,null);

    }
    public function addHtml(string $html)
    {
        $this->mpdf->WriteHTML($html);
    }
    public function addHtmlUnlimitedHeight(string $html)
    {
        $this->mpdf->WriteHTML($html, HTMLParserMode::DEFAULT_MODE, true,false);

    }
    public function setPageUnlimitedHeight(float $width){
        // The $p needs to be passed by reference
        $p = 'P';

        $this->mpdf->_setPageSize(array($width, $this->mpdf->y), $p);
    }
    public function addCss(string $stylesheet)
    {
        $this->mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
    }
    public function setSourceFile(string $source)
    {
        $pagecount = $this->mpdf->SetSourceFile($source);
        for($page = 1; $page <= $pagecount; $page++){
            $tplId = $this->mpdf->importPage($page);
            $this->mpdf->useTemplate($tplId);
            if($page < $pagecount){
                $this->mpdf->AddPage();
            }

        }


    }
    public function getPdfOutput($name = '', $dest = ''): ?string
    {
        return $this->mpdf->Output($name, $dest);
    }
}