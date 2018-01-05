<?php// @codingStandardsIgnoreFileuse Migrations\AbstractMigration;class CreatingLocationSlugForEvents extends AbstractMigration{    /**     * Change Method.     *     * More information on this method is available here:     * http://docs.phinx.org/en/latest/migrations.html#the-change-method     * @return void     */    public function up()    {        // clean up errors in inputting locations        // locations around town        $this->execute('UPDATE events SET location_details="Music Room", location="Cornerstone Center for the Arts" WHERE id="5"');        $this->execute('UPDATE events SET location="The Mark III Tap Room", address="306 S. Walnut St." WHERE id="1130" OR location="The Mark III Taproom"');        $this->execute('UPDATE events SET location="Northside Church of the Nazarene", address="3801 N. Wheeling Ave." WHERE id="4691"');        $this->execute('UPDATE events SET location="Be Here Now", address="505 N. Dill St." WHERE id="2853"');        $this->execute('UPDATE events SET location="Cornerstone Center for the Arts", address="520 E. Main St." WHERE id="4561"');        $this->execute('UPDATE events SET location="Lotus Yoga Studio", address="814 W. White River Blvd." WHERE id="4116"');        $this->execute('UPDATE events SET location="AMC Showplace Muncie 12" WHERE id="3421"');        $this->execute('UPDATE events SET location="Ball Memorial Hospital", location_details="Auditorium" WHERE id="30"');        $this->execute('UPDATE events SET location="Ball Memorial Hospital", location_details="South Tower, John Fisher Heart Center lounge" WHERE id="2163"');        $this->execute('UPDATE events SET location="Cardinal Greenway Depot/Trail" WHERE location like "Cardinal Greenway Depot%" OR location="Cardinal Greenways, Wysor Street Depot" OR location="Historic Wysor St. Depot"');        $this->execute('UPDATE events SET location="Carnagie Public Library" WHERE location like "Carnagie Library%"');        $this->execute('UPDATE events SET location="Connection Corner" WHERE id="4362"');        $this->execute('UPDATE events SET location="Delaware County Fairgrounds", location_details="Community Building" WHERE id="1563"');        $this->execute('UPDATE events SET location="Delaware County Fairgrounds", location_details="Memorial Building" WHERE id="1693"');        $this->execute('UPDATE events SET location="Delaware County Fairgrounds" WHERE location="Delaware County Fair Grounds"');        $this->execute('UPDATE events SET location="Downtown", location_details="Old National Lot" WHERE id="2291" OR id="3032"');        $this->execute('UPDATE events SET location="Elm Street Brewing Company" WHERE location="Elm Street Brew Pub"');        $this->execute('UPDATE events SET location="Elm Street Brewing Company" WHERE location="Elm Street Brewing Company."');        $this->execute('UPDATE events SET location="Full Circle Arts Co-op" WHERE location like "Full Circle Arts Co-op%"');        $this->execute('UPDATE events SET location="Gallery 308" WHERE location="GALLERY 308"');        $this->execute('UPDATE events SET location="Gibson Roller Skating Arena" WHERE location like "Gibson%"');        $this->execute('UPDATE events SET location="Glick Center for Glass" WHERE location like "Glick%"');        $this->execute('UPDATE events SET location="Heath Cemetary" WHERE location like "Heath%"');        $this->execute('UPDATE events SET location="The Heorot Pub & Draught House" WHERE id="1851" OR location like "Heorot%" OR location like "The Heorot%"');        $this->execute('UPDATE events SET location="Innovation Connector", location_details="Room 114" WHERE id="157"');        $this->execute('UPDATE events SET location="Innovation Connector" WHERE id="3747" OR id="4974"');        $this->execute('UPDATE events SET location="Kennedy Public Library" WHERE id="1854"');        $this->execute('UPDATE events SET location="Muncie Mall" location_details="Macy\'s" WHERE location="Macy\'s - Muncie Mall"');        $this->execute('UPDATE events SET location="Muncie Mall" location_details="Parking Lot" WHERE id="4862"');        $this->execute('UPDATE events SET location="Maring-Hunt Public Library" WHERE location like "Maring%"');        $this->execute('UPDATE events SET location="McCulloch Park" WHERE location like "McCullo%"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="The Lawn" WHERE location="Minnestrista"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Oakhurst Gardens" WHERE location="Minnetrista Oakhurst Gardens"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Oakhurst Mansion" WHERE id="4216"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Orchard Shop" WHERE location="Minnetrista Orchard Shop"');        $this->execute('UPDATE events SET location="Minnetrista" WHERE location="Minnetrista Center"');        $this->execute('UPDATE events SET location="Minnetrista" WHERE location="Minnetrista Cultural Center"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Outdoor Stage" WHERE id="4137"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Indiana Room" WHERE id="1174"');        $this->execute('UPDATE events SET location="Minnetrista", location_details="Outside the Orchard Shop" WHERE location="Minnestrista (outside the Orchard Shop)"');        $this->execute('UPDATE events SET location="Motivate Our Minds"  WHERE location="MOMS"');        $this->execute('UPDATE events SET location="Muncie Area Career Center", location_details="President\'s Room"  WHERE id="32"');        $this->execute('UPDATE events SET location="Muncie Central High School", location_details="Auditorium"  WHERE id="3120" OR id="156"');        $this->execute('UPDATE events SET location="Muncie Civic Theatre", location_details="Main Stage"  WHERE location="Muncie Civic Theatre Main Stage"');        $this->execute('UPDATE events SET location="Northside Middle School" WHERE location like "Northside Middle School%"');        $this->execute('UPDATE events SET location="Southside High School", location_details="Cafeteria" WHERE id="31"');        $this->execute('UPDATE events SET location="St. Lawrence Catholic Church", location_details="West Side of St. Lawrence/Inspire Academy Building" WHERE id="4350"');        $this->execute('UPDATE events SET location="Westside Park" WHERE id="4350" OR location="West Side Park" OR location="Westside Park in Muncie"');        $this->execute('UPDATE events SET location="Westview Elementary School" WHERE location like "Westview Elementary%"');        $this->execute('UPDATE events SET location="Washington Street Festival" WHERE id="4142"');        $this->execute('UPDATE events SET location="Valhalla" WHERE location like "The Valhalla%" OR location like "Valhalla%"');        $this->execute('UPDATE events SET location="Vera Mae\'s Bistro" WHERE id="2457"');        $this->execute('UPDATE events SET location="YWCA" location_details="Community Room" WHERE id="2319"');        $this->execute('UPDATE events SET location="YWCA Women\'s Shelter" WHERE id="4767" OR id="4849"');        $this->execute('UPDATE events SET location="Yorktown Middle School", location_details="Gym" WHERE id="4692"');        $this->execute('UPDATE events SET location_details="Cafeteria" WHERE id="34"');        // ivy tech things        $this->execute('UPDATE events SET location="Fisher Building", location_details="Auditorium" WHERE id="4448" OR id="4435"');        // oh wow, here's all the different ways ppl have learned to enter BSU buildings over the years        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 101", address="1001 N. McKinley Ave." WHERE id="1011"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 125", address="1001 N. McKinley Ave." WHERE id="962"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 175", address="1001 N. McKinley Ave." WHERE location="AJ 175" OR location="Art and Journalism Building, Ball State University Room 175" OR location="Art and Journalism Building, Ball State University room 175" OR location="The Art and Journalism Building, Ball State University, room 175" OR location="Ball State University AJ175" OR location="Ball State University, AJ 175" OR id="1167" OR id="2294" OR id="3355"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 225", address="1001 N. McKinley Ave." WHERE location="Art and Journalism Building, Ball State University Room 225" OR location="Ball State University, AJ 225" OR id="301" OR id="3594" OR id="3930"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 289", address="1001 N. McKinley Ave." WHERE id="1219"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium", address="1001 N. McKinley Ave." WHERE location="Ball State University Atrium" OR location like "The Atrium%"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium Gallery", address="1001 N. McKinley Ave." WHERE id="3635" OR location="The Atrium Gallery"');        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium Patio", address="1001 N. McKinley Ave." WHERE id="1292"');        $this->execute('UPDATE events SET location="Architecture Building, Ball State University", location_details="Room 100" WHERE id="183" OR id="1562" OR id="1289"');        $this->execute('UPDATE events SET location="Museum of Art, Ball State University", location_details="Room 217" WHERE location="Art Museum Room 217, Ball State University"');        $this->execute('UPDATE events SET location="Museum of Art, Ball State University" WHERE id="129" OR id="158" OR location="Ball State Museum of Art" OR location="Ball State University Museum of Art" OR location="Museum of Art, Ball State University, Ball State University" OR location="Museum of Art, Ball State University, Fine Arts Building" OR location like "David Owsley Museum of Art%"');        $this->execute('UPDATE events SET location="Museum of Art, Ball State University", location_details="Recital Hall" WHERE id="1490" OR id="1709"');        $this->execute('UPDATE events SET location="Museum of Art, Ball State University", location_details="Brown Study Room" WHERE id="1113"');        $this->execute('UPDATE events SET location="Arts Terrace, Ball State University", address="2021 Riverside Ave." WHERE location="BSU Arts Terrace" OR location="Ball State University Arts Terrace"');        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 104" WHERE id="94" OR id="259" OR id="281" OR id="1714" or id="335"');        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 201" WHERE id="695" OR id="427"');        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 215" WHERE id="1166"');        $this->execute('UPDATE events SET location="Bracken Library, Ball State University" WHERE location="Bracken Library"');        $this->execute('UPDATE events SET location="Burkhardt Building, Ball State University", location_details="Room 220" WHERE location like "Burkhardt Building rm 220%"');        $this->execute('UPDATE events SET location="Burkhardt Building, Ball State University", location_details="Room 109" WHERE id="1725"');        $this->execute('UPDATE events SET location="Burkhardt Building, Ball State University" WHERE id="950" OR id="949"');        $this->execute('UPDATE events SET location="Burkhardt Building, Ball State University", location_details="Room 108" WHERE id="331" OR id="332"');        $this->execute('UPDATE events SET location="Cooper Physical Science Building, Ball State University", location_details="Room 160" WHERE id="1291"');        $this->execute('UPDATE events SET location="Music Instruction Building, Ball State University", location_details="Room 152" WHERE id="250"');        $this->execute('UPDATE events SET location="Music Instruction Building, Ball State University" WHERE id="3359" OR id="3371"');        $this->execute('UPDATE events SET location="Sursa Hall, Ball State University" WHERE id="1164" OR location like "Sursa Performance Hall%"');        $this->execute('UPDATE events SET location="Worthen Arena, Ball State University", location_details="Lounge" WHERE id="1290"');        $this->execute('UPDATE events SET location="Worthen Arena, Ball State University" WHERE id="4449"');        $this->execute('UPDATE events SET location="Varsity Softball Complex, Ball State University" WHERE title="BSU Women\'s Softball"');        $this->execute('UPDATE events SET location="Anthony Recreation Fields, Ball State University" WHERE title="Ball State Women\'s Soccer"');        $this->execute('UPDATE events SET location="Briner Sports Complex, Ball State University" WHERE title="Ball State Field Hockey"');        $this->execute('UPDATE events SET location="Aquatic Center, Ball State University" WHERE id="4316"');        $this->execute('UPDATE events SET location="Frog Baby, Ball State University" WHERE id="2207"');        $this->execute('UPDATE events SET location="LaFollette Field, Ball State University" WHERE id="438" OR id="3839"');        $this->execute('UPDATE events SET location="Letterman Building, Ball State University", location_details="Room 125" WHERE id="1298" OR id="1102" OR id="526" OR id="3325" OR id="184" OR location="Ball State, LB 125"');        $this->execute('UPDATE events SET location="Ball Gymnasium, Ball State University" WHERE id="4118"');        $this->execute('UPDATE events SET location="Strother Theatre, Ball State University" WHERE location="Strother Theater"');        $this->execute('UPDATE events SET location="Alumni Center, Ball State University", location_details="Meeting Room 1" WHERE id="1497" OR id="2967" OR id="3861"');        $this->execute('UPDATE events SET location="University Green, Ball State University" WHERE id="151" OR id="1314"');        $this->execute('UPDATE events SET location="University Theatre, Ball State University" WHERE location="University Theatre"');        $this->execute('UPDATE events SET location="North Quad, Ball State University", location_details="Room 039" WHERE id="1721"');        $this->execute('UPDATE events SET location="North Quad, Ball State University", location_details="On the Green" WHERE id="2325" OR id="2326"');        $this->execute('UPDATE events SET location="The Quad, Ball State University" WHERE id="1974"');        $this->execute('UPDATE events SET location="Multicultural Center, Ball State University" WHERE id="407"');        $this->execute('UPDATE events SET location="Teachers College, Ball State University", location_details="Room 102" WHERE id="1294"');        $this->execute('UPDATE events SET location="Teachers College, Ball State University", location_details="Room 200B" WHERE id="452"');        $this->execute('UPDATE events SET location="Teachers College, Ball State University" WHERE id="3370"');        $this->execute('UPDATE events SET location="E.B. and Bertha C. Ball Center" WHERE location like "E.B.%"');        $this->execute('UPDATE events SET location="Pruis Hall" WHERE location like "Pruis Hall%"');        $this->execute('UPDATE events SET location="Rinard Orchid Greenhouse" WHERE location like "Rinard%"');        $this->execute('UPDATE events SET location="Student Center, Ball State University" WHERE id="398" OR location="BSU Student Center" OR location="BSU Student Center, Registration on Second Floor" OR id="2021"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 310" WHERE location="BSU Student Center room 310" OR id="288"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Forum Room" WHERE location="BSU Student Center, Forum Room" OR id="1176"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 301" WHERE location="BSU Student Center, Room 301"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 102" WHERE id="300"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Pineshelf Room" WHERE id="3907"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 303" WHERE location="BSU Student Center, Room 303"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Rooms 310A & B" WHERE location="BSU Student Center, Rooms 310A and B"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Outside" WHERE id="2049"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Ballroom" WHERE id="2234" OR id="2235" OR id="1073"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Cardinal Hall" WHERE id="1675" OR id="1717"');        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="The Tally" WHERE location="Student Center Tally"');        $this->execute('UPDATE events SET location="Ball State University (General)" WHERE id="1300"');        // general location names...        $this->execute('UPDATE events SET location="Multiple Locations" WHERE id="4276" OR id="4554" OR id="1116" OR id="1117" OR series_id="275" OR id="3235" OR id="4991" OR id="4858" OR id="4276" OR id="4554" OR location like "Downtown and the Ball State%"');        $this->execute('UPDATE events SET location="To be determined" WHERE id="3483"');        // here are events that are not in Muncie        $this->execute('DELETE FROM events WHERE id="4765"');        $this->execute('DELETE FROM events WHERE location="Stable Studios"');        $events = $this->table('events');        $events            ->addColumn('location_slug', 'string', ['after' => 'location_details', 'limit' => 20, 'null' => false])            ->save();        $stmt = $this->query('SELECT * FROM events');        $events = $stmt->fetchAll();        foreach ($events as $event) {            $id = $event['id'];            $locationSlug = strtolower($event['location']);            $locationSlug = substr($locationSlug, 0, 20);            $locationSlug = str_replace('/', ' ', $locationSlug);            $locationSlug = preg_replace("/[^A-Za-z0-9 ]/", '', $locationSlug);            $locationSlug = str_replace("   ", ' ', $locationSlug);            $locationSlug = str_replace("  ", ' ', $locationSlug);            $locationSlug = str_replace(' ', '-', $locationSlug);            if (substr($locationSlug, -1) == '-') {                $locationSlug = substr($locationSlug, 0, -1);            }            $this->execute('UPDATE events SET location_slug="' . $locationSlug . '" WHERE id="' . $id . '"');        }    }    /**     * Change Method.     *     * More information on this method is available here:     * http://docs.phinx.org/en/latest/migrations.html#the-change-method     * @return void     */    public function down()    {        $events = $this->table('events');        $events            ->removeColumn('location_slug')            ->save();    }}