<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ExcelExportService
{
    /**
     * @var array $data
     */
    protected array $data;

    /**
     * @var string $name
     */
    protected string $name;

    /**
     * @var Spreadsheet
     */
    protected Spreadsheet $spreadsheet;

    /**
     * @var array $headers
     */
    protected array $headers;

    /**
     * Indica si el archivo tiene cabeceras o no. Se setea automáticamente al llamar al método setheaders.
     */
    protected bool $withHeaders;

    public function __construct()
    {
        $this->data = [];
        $this->name = "Default name";
        $this->spreadsheet = new Spreadsheet(); // Iniciamos la hoja de cálculo
        $this->withHeaders = false;
    }

    /**
     * Setea el valor de los datos a exportar
     *
     * @param array $data
     */
    public function setArrayData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Setea el nombre del archivo.
     *
     * @param string $name
     *
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param $title
     * @param $subject
     * @param $creator
     * @param $description
     * @param $keywords
     * @param $category
     */
    public function setMetadata($title, $subject, $creator, $description, $keywords, $category): void
    {
        // Set document properties
        $this->spreadsheet->getProperties()->setCreator($creator)
            ->setLastModifiedBy('System')
            ->setTitle($title)
            ->setSubject($subject)
            ->setDescription($description)
            ->setKeywords($keywords)
            ->setCategory($category);
    }

    /**
     * Setea las cabeceras de la tabla en la hoja de cálculo
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
        $this->withHeaders = true;
    }

    /*
    private function isValid() {
        if($this->withHeaders and count($this->headers) != count($this->data)){
            throw new Exception("Size of headers is diferent of data.");
        }
    }
    */

    /**
     * @param int $index Índice de la hoja a añadir la cabecera
     * @throws Exception
     */
    private function buildHeaders(int $index): void
    {
        foreach ($this->headers as $indexHeader => $value) {
            $this->spreadsheet->setActiveSheetIndex($index)->setCellValue($this->getNameFromNumber($indexHeader)."1", $value);
        }
    }

    /**
     * Pinta los datos en el archivo excel
     * @param int $since Indica desde que fila se empieza a escribir los datos
     * @param int $sheetIndex Indica el índice de la página en donde se va a escribir
     * @throws Exception
     */
    private function buildData(int $since, int $sheetIndex = 0): void
    {
        $currentLine = $since;
        $this->spreadsheet
            ->setActiveSheetIndex($sheetIndex);
        foreach ($this->data as $indexLine => $line) {
            $currentColumn = 0;
            foreach ($line as $indexColumn => $column) {
                $this->spreadsheet
                    ->getActiveSheet()
                    ->setCellValue(
                        $this->getNameFromNumber($currentColumn).$currentLine,
                        $column
                    );
                $currentColumn++;
            }
            $currentLine++;
        }
    }

    /**
     * Exporta un array preestablecido previamente con la funcion setArrayData
     * @throws Exception
     */
    public function exportArrayToExcel(): bool|string
    {
        // Creamos el archivo excel a exportar
        // Create new Spreadsheet object
        //$spreadsheet = new Spreadsheet();
        $this->spreadsheet->setActiveSheetIndex(0)->setTitle($this->name);
        if ($this->withHeaders) {
            $this->buildHeaders(0);
            $this->buildData(2,0);
        } else {
            $this->buildData(1,0);
        }
        return $this->generateResponse();
    }

    /**
     * Genera la respuesta para devolver un archivo en formato response.
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateResponse(): bool|string
    {
        // Creamos el generador del archivo
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        // Generamos el objeto de respuesta
        $writer->save('documento.xlsx');
        $contenido = file_get_contents('documento.xlsx');
        unlink('documento.xlsx');
        return $contenido;
    }

    /** Función para obtener la letra de columna a partir de un índice de columna de un array. 0 == A, 1 == B
     * @param $num
     * @return string
     */
    public function getNameFromNumber($num): string
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    /**
     * @return IWriter
     * @throws Exception
     */
    public function returnFileWriter(): IWriter
    {
        // Creamos el generador del archivo
        return IOFactory::createWriter($this->spreadsheet, 'Xlsx');
    }
}