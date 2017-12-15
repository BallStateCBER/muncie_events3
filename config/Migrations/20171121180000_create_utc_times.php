<?php
// @codingStandardsIgnoreFile

use Phinx\Migration\AbstractMigration;

class CreateUtcTimes extends AbstractMigration
{
    /**
     * migrate up
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('events');
        $table->changeColumn('date', 'date', ['limit' => 10, 'default' => '1969-12-31'])
            ->addColumn('start', 'datetime', ['after' => 'date', 'limit' => 18, 'default' => '1969-12-31 00:00:00'])
            ->addColumn('end', 'datetime', ['after' => 'start', 'limit' => 18, 'null' => true])
            ->update();

        $stmt = $this->query("SELECT * FROM events");
        $events = $stmt->fetchAll();

        foreach ($events as $event) {
            $id = $event['id'];

            if (date('I', strtotime($event['date'])) == 1) {
                $dst = ' + 4 hours';
            }
            if (date('I', strtotime($event['date'])) == 0) {
                $dst = ' + 5 hours';
            }

            $start =
                date('Y-m-d', strtotime($event['date'] . ' ' . $event['time_start'] . $dst)) . ' ' . date('H:i:s', strtotime($event['date'] . ' ' . $event['time_start'] . $dst));
            $endVal =
                date('Y-m-d', strtotime($event['date'] . ' ' . $event['time_end'] . $dst)) . ' ' . date('H:i:s', strtotime($event['date'] . ' ' . $event['time_end'] . $dst));

            $end = '';
            if ($endVal == '0000-00-00 00:00:00' || $endVal == null || $event['time_end'] == null) {
                $this->execute("UPDATE events SET start='$start', end=null WHERE id='$id'");
            } elseif ($endVal < $start) {
                $end =
                    date('Y-m-d', strtotime($event['date'] . ' ' . $event['time_end'] . $dst . '+1 day')) . ' ' . date('H:i:s', strtotime($event['date'] . ' ' . $event['time_end'] . $dst . '+1 day'));

                $this->execute("UPDATE events SET start='$start', end='$end' WHERE id='$id'");
            } else {
                $end = $endVal;
                $this->execute("UPDATE events SET start='$start', end='$end' WHERE id='$id'");
            }

            print_r("|");
        }

        // need to move the cols...
        /*$this->execute("ALTER TABLE events CHANGE COLUMN start AFTER time_end,
                                CHANGE COLUMN end AFTER start");*/

        print_r(" All events have been updated!");
    }

    /**
     * migrate down
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('events');

        $table->removeColumn('start')
            ->removeColumn('end')
            ->update();

        print_r(" All events have been updated!");
    }
}