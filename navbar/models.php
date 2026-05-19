<?php

class CruiseShip {
    private string $name;
    private string $image;
    private array $highlights;

    public function __construct(string $name, string $image, array $highlights) {
        $this->name = $name;
        $this->image = $image;
        $this->highlights = $highlights;
    }

   
    public function getName(): string { return $this->name; }
    public function getImage(): string { return $this->image; }
    public function getHighlights(): array { return $this->highlights; }

  
    public function setName(string $name): void { $this->name = $name; }
    public function setImage(string $image): void { $this->image = $image; }
    public function setHighlights(array $highlights): void { $this->highlights = $highlights; }
}

class Destination {
    private string $name;
    private string $image;
    private bool $featured;

    public function __construct(string $name, string $image, bool $featured) {
        $this->name = $name;
        $this->image = $image;
        $this->featured = $featured;
    }

    public function getName(): string { return $this->name; }
    public function getImage(): string { return $this->image; }
    public function isFeatured(): bool { return $this->featured; }

    public function setName(string $name): void { $this->name = $name; }
    public function setImage(string $image): void { $this->image = $image; }
    public function setFeatured(bool $featured): void { $this->featured = $featured; }
}

class Tier {
    private string $name;
    private string $image;

    public function __construct(string $name, string $image) {
        $this->name = $name;
        $this->image = $image;
    }

    public function getName(): string { return $this->name; }
    public function getImage(): string { return $this->image; }

    public function setName(string $name): void { $this->name = $name; }
    public function setImage(string $image): void { $this->image = $image; }
}