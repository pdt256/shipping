<?php namespace pdt256\Shipping;

class Package
{
    const FEDEX_YOUR_PACKAGING = 'YOUR_PACKAGING';
    const USPS_CONTAINER_RECTANGULAR = 'RECTANGULAR';
    const USPS_SIZE_LARGE = 'LARGE';

    protected $weight;
    protected $width;
    protected $length;
    protected $height;
    protected $packaging;
    protected $size_classification;

    /**
     * @return mixed
     */
    public function getSizeClassification()
    {
        return $this->size_classification;
    }

    /**
     * @param mixed $size_classification
     * @return $this
     */
    public function setSizeClassification($size_classification)
    {
        $this->size_classification = $size_classification;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackaging()
    {
        return $this->packaging;
    }

    /**
     * @param mixed $packaging
     * @return $this
     */
    public function setPackaging($packaging)
    {
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }
}
