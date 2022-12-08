<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;


trait TimestampTrait 
{

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    
    }

    public function geHumanCreatedAt()
    {
        $carbon = new Carbon($this->created_at);
        return $carbon->locale('fr_FR')->diffForHumans();
    }

    public function geHumanUpdatedAt()
    {
        $carbon = new Carbon($this->updated_at);
        return $carbon->locale('fr_FR')->diffForHumans();
    }

}