<?php

namespace BlockHorizons\BlockPets\pets;

use pocketmine\math\Vector3;

abstract class HoveringPet extends BasePet {

	public $gravity = 0;
	protected $flyHeight = 0;

	public function onUpdate($currentTick) {
		$petOwner = $this->getPetOwner();
		parent::onUpdate($currentTick);
		if($petOwner === null || $this->isRidden()) {
			return false;
		}

		$x = $petOwner->x - $this->x;
		$y = $petOwner->y + 1 - $this->y;
		$z = $petOwner->z - $this->z;

		if($x * $x + $z * $z < 8) {
			$this->motionX = 0;
			$this->motionZ = 0;
		} else {
			$this->motionX = $this->getSpeed() * 0.15 * ($x / (abs($x) + abs($z)));
			$this->motionZ = $this->getSpeed() * 0.15 * ($z / (abs($x) + abs($z)));
		}

		if($y !== 0 && abs($y) <= 1) {
			$this->motionY = $this->getSpeed() * 0.15 * ($y / abs($y));
		}

		$this->yaw = rad2deg(atan2(-$x, $z));
		if($this->getNetworkId() === 53) {
			$this->yaw += 180;
		}
		$this->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
		$this->move($this->motionX, $this->motionY, $this->motionZ);

		$this->updateMovement();
		parent::onUpdate($currentTick);
		return true;
	}

	public function doRidingMovement($motionX, $motionZ) {
		$rider = $this->getPetOwner();

		$this->pitch = $rider->pitch;
		$this->yaw = $this->getNetworkId() === 53 ? $rider->yaw + 180 : $rider->yaw;

		$x = $this->getDirectionVector()->x / 2 * $this->getSpeed();
		$z = $this->getDirectionVector()->z / 2 * $this->getSpeed();
		$y = $rider->getDirectionVector()->y / 2 * $this->getSpeed();

		$finalMotion = [0, 0, 0];
		switch($motionZ) {
			case 1:
				$finalMotion = [$x, $y, $z];
				break;
			case -1:
				$finalMotion = [-$x, $y, $z];
				break;
		}
		switch($motionX) {
			case 1:
				$finalMotion = [$z, $y, $x];
				break;
			case -1:
				$finalMotion = [-$z, $y, $x];
				break;
		}

		if(($y !== 0.0 && $this->distance(new Vector3($this->x, $this->level->getHighestBlockAt($this->x, $this->z), $this->z)) <= $this->flyHeight) || $y < 0) {
			$this->motionY = $this->getSpeed() * 0.2 * ($y / abs($y * 2));
		}
		$this->move($finalMotion[0], $this->motionY, $finalMotion[2]);

		$this->updateMovement();
	}
}
