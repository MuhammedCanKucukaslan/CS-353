<?php

class TourSection
{

    const TOUR_SECTION_TABLE = "tour_section";
    const TOUR_TABLE = "tour";
    const STATUS_APPROVED = "approved";
    const STATUS_PENDING = "pending";
    const STATUS_REJECTED = "rejected";

    private int $t_id;
    private int $ts_id;
    private string $place;
    private string $type;
    private DateTime $start_date;
    private DateTime $end_date;



    public static function makeTourSection(PDO $pdo, int $ts_id) : TourSection {
        $ts = new TourSection();
        $sql = "SELECT * FROM ".TourSection::TOUR_SECTION_TABLE." natural join ".TourSection::TOUR_TABLE." WHERE ts_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ts_id]);
        $row = $stmt->fetch();

        $ts->setTId($row['t_id']);
        $ts->setTsId($row['ts_id']);
        $ts->setPlace($row['place']);
        $ts->setType($row['type']);
        $ts->setStartDate(new DateTime($row['start_date']));
        $ts->setEndDate(new DateTime($row['end_date']));

        return $ts;
    }

    public function printTourDetails() {
        // print the tour details big
        echo "<h2>Tour Details</h2>";
        echo "<ul>";
        echo "<li>Tour ID: ".$this->getTId()."</li>";
        echo "<li>Tour Section ID: ".$this->getTsId()."</li>";
        echo "<li>Place: ".$this->getPlace()."</li>";
        echo "<li>Type: ".$this->getType()."</li>";
        echo "<li>Start Date: ".$this->getStartDate()->format('Y-m-d')."</li>";
        echo "<li>End Date: ".$this->getEndDate()->format('Y-m-d')."</li>";
        echo "</ul>";
    }

    /**
     * @return int
     */
    public function getTId(): int
    {
        return $this->t_id;
    }

    /**
     * @param int $t_id
     */
    public function setTId(int $t_id): void
    {
        $this->t_id = $t_id;
    }

    /**
     * @return int
     */
    public function getTsId(): int
    {
        return $this->ts_id;
    }

    /**
     * @param int $ts_id
     */
    public function setTsId(int $ts_id): void
    {
        $this->ts_id = $ts_id;
    }

    /**
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }

    /**
     * @param string $place
     */
    public function setPlace(string $place): void
    {
        $this->place = $place;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->start_date;
    }

    /**
     * @param DateTime $start_date
     */
    public function setStartDate(DateTime $start_date): void
    {
        $this->start_date = $start_date;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->end_date;
    }

    /**
     * @param DateTime $end_date
     */
    public function setEndDate(DateTime $end_date): void
    {
        $this->end_date = $end_date;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->getType();
    }
    public function __toString()
    {
        return "TourSection: ".$this->getTsId()." ".$this->getPlace()." ".$this->getType()." ".$this->getStartDate()->format('Y-m-d')." ".$this->getEndDate()->format('Y-m-d');
    }

    /*
    * Get the reservation details for this tour section
     * array of arrays with the following keys:
     * c_id, res_id, ts_id, number, status, bill, name, lastname, email
    */
    public function getReservations(PDO $pdo, $status = null) : ?array {
        //  c_id 	res_id 	ts_id 	e_id 	number 	status 	isRated 	reason 	bill 	name 	lastname 	email
        $sql = "SELECT * FROM reservation natural join thecustomer t WHERE ts_id = :ts_id";
        if ($status != null) {
            $sql .= " AND status = :status";
        }
        $stmt = $pdo->prepare($sql);
        if($status != null) {
            $stmt->execute([":ts_id" => $this->getTsId(), ":status" => $status]);
        } else {
            $stmt->execute([":ts_id" => $this->getTsId()]);
        }
        return $stmt->fetchAll();            
    }

    public function getTourGuide(PDO $pdo, $status = "approved") : ?array {
        // get the tour guide offerings for this tour section
        // array of arrays with the following keys:
        // tg_id, ts_id, status, reason, name, lastname, email, birthday, registration
        $sql = "SELECT * FROM tour_guide natural join guides g WHERE ts_id = :ts_id and status = :status";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ts_id" => $this->getTsId(), ":status" => $status]);
        return $stmt->fetchAll();
    }
}