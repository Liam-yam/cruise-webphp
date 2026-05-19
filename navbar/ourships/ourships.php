<?php

class Amenities {

  
    private $openTime = "8:00 AM - 6:00 PM";

  
    protected function formatTitle($title) {
        return strtoupper($title);
    }

   
    public function getSpaInfo() {
        return [
            "title" => $this->formatTitle("Spa and Salon"),
            "description" => "Guests can unwind with a soothing hot stone massage, offering a relaxing and rejuvenating wellness experience onboard.",
            "time" => $this->openTime
        ];
    }

    public function getFitnessInfo() {
        return [
            "title" => $this->formatTitle("Sports and Fitness"),
            "description" => "Guests can stay active using modern fitness equipment while enjoying scenic ocean views during their workout.",
            "time" => $this->openTime
        ];
    }

    public function getNightInfo() {
        return [
            "title" => $this->formatTitle("Nightclub Lounges"),
            "description" => "Guests can relax and socialize in a vibrant lounge atmosphere while enjoying drinks and entertainment at sea.",
            "time" => $this->openTime
        ];
    }
}

?>