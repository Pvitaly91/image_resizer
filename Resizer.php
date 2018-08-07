<?

class File {

    protected $source = "", $source_root = "", $root_path = "";
    private $output_file_name, $output_dir, $file, $output_file_path,$type= "txt";

    public function __construct($source = false) {

        $this->setOutputDir("/");

        $this->setRootPath();
        // app path to file
        $this->source = $source;
        // root path to source filel
        $this->source_root = $this->root_path . $this->source;
    }
    protected  function setTtpe($type){
        $this->type = $type;
        return $this;
    }
    protected function setRootPath($root_path = false) {
        if ($root_path) {
            $this->root_path = $root_path;
        }

        $this->root_path = $_SERVER["DOCUMENT_ROOT"];
        return $this;
    }

    private function makeOutputFilePath() {
        if ($this->output_file_name)
            $this->output_file_path = $this->root_path . $this->output_dir . $this->output_file_name.".".$this->type;
        return $this;
    }

    function toFile() {

        return file_put_contents($this->output_file_path, $this->file);
    }

    function setOutPutFileName($outputFileName) {
        $this->output_file_name = $outputFileName;
        $this->makeOutputFilePath();
        return $this;
    }

    function getOutputFileName() {
        return $this->output_file_name;
    }

    function setOutputDir($dirName) {
        $this->output_dir = $dirName;
        return $this;
    }

    function getOutputDir() {
        return $this->output_dir;
    }

    function setFile($file) {
        $this->file = $file;
        return $this;
    }

    function getFile() {
        return $this->file;
    }

    function getOutputFilePath() {
        return $this->output_file_path;
    }

}

abstract class image extends File {

    protected $thumb, $source_img;
    private $resize_koef = 1;
    private $newwidth, $newheight;
    private $cur_witdh, $cur_height;

    public function __construct($source) {
        parent::__construct($source);
        // get original image size
        $this->getCurImgSize();
        //load img
        $this->load();
    }

    /**
     * set source image path
     * @param type $source
     */
    protected function setSorce($source) {
        $this->source = $source;
        return $this;
    }

    function setNewWidth($newwidth) {
        $this->newwidth = $newwidth;
        return $this;
    }

    function setNewHeight($newheight) {
        $this->newheight = $newheight;
        return $this;
    }

    function setResizeKoeficient($koef) {
        try {
            if ($koef > 0) {
                $this->resize_koef = $koef;
                $this->setNewImgSize();
                return $this;
            } else {
                throw new Exception("resize koeficient must be > 0");
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    /**
     * create empty image by new size
     */
    protected function createBrush() {
        $this->thumb = imagecreatetruecolor($this->newwidth, $this->newheight);
    }

    abstract protected function load();

    /**
     * get original image size
     */
    private function getCurImgSize() {

        list($this->cur_witdh, $this->cur_height) = getimagesize($this->source_root);
    }

    private function setNewImgSize() {

        $this->setNewWidth($this->cur_witdh * $this->resize_koef);
        $this->setNewHeight($this->cur_height * $this->resize_koef);
        $this->createBrush();
    }

    function resize() {
         imagecopyresampled($this->thumb, $this->source_img, 0, 0, 0, 0, $this->newwidth, $this->newheight, $this->cur_witdh, $this->cur_height);
    }
    function toFile($fileName) {
        $this->setOutputDir("/upload/")->setOutPutFileName($fileName);
    }
    /**
     * render resized image
     */
    abstract function render();
}

class Jpg extends image {
    function __construct($source) {
        parent::__construct($source);
        $this->setTtpe("jpg");
    }
    function resize() {
        imagecopyresized($this->thumb, $this->source_img, 0, 0, 0, 0, $this->newwidth, $this->newheight, $this->cur_witdh, $this->cur_height);
    }

    protected function load() {
        $this->source_img = imagecreatefromjpeg($this->source_root);
    }

    function render() {
        $this->resize();
        header('Content-Type: image/jpeg');
        imagejpeg($this->thumb);
    }

}

class Png extends image {
    function __construct($source) {
        parent::__construct($source);
        $this->setTtpe("png");
    }
    protected function createBrush() {
        parent::createBrush();
        
        imagecolortransparent($this->thumb, imagecolorallocatealpha($this->thumb, 0, 0, 0, 127));
        imagealphablending($this->thumb, false);
        imagesavealpha($this->thumb, true);
    }

    protected function load() {
        $this->source_img = imagecreatefrompng($this->source_root);
        imagealphablending($this->source_img, true);
    }

    function render() {
        $this->resize();
        header('Content-Type: image/png');

        imagepng($this->thumb);
    }
    function toFile($fileName) {
	$this->resize();
        $this->setOutputDir("/upload/")->setOutPutFileName($fileName);    
	imagepng($this->thumb,$this->getOutputFilePath());
    }

}
