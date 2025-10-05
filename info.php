<?php
ob_start();
// START OF PAGE CONTENT AND LOGIC.
?>

<style>
.content {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction:column;
}
</style>

<div> 
(This whole page is satire.)
<br>
<em>Information about who (<a href="https://en.wikipedia.org/wiki/Down_syndrome">me</a>), the mantainer of this service, is.</em>
<br>
<br>
I am <a href="https://www.windows93.net/c/programs/acidBox93/">acidbox</a>, previously known as stuxvii (before i found out that <a href="https://www.youtube.com/channel/UCVGGQM62yoq_-gsuioGISKA">someone had that nick but with a y</a>), <a href="https://www.youtube.com/watch?v=RIZWzT_RL6Y&t=64">pussyconsumer69</a>, <a href="https://www.youtube.com/watch?v=UaG7L2z0QHY&t=54">skibiditoiletsigmaslippers</a>, <a href="https://web.archive.org/web/20111229202221/http://www.georgerrmartin.com/">the guy who made that one series called the game of thrones</a>,
<a href="https://youtu.be/W9DST-6jIBU&t=464">the guy who brought bonzi buddy back to relevance</a>, <a href="https://en.wikipedia.org/wiki/God">the guy who made all those siIvagunner mashups</a>, 
<a href="https://www.youtube.com/@markiplier">that one guy who got 40 million subscribers on youtube from recording <a href="https://en.wiktionary.org/wiki/misgender">himself</a> getting scared at children's games</a>, 
<a href="https://www.youtube.com/watch?v=7ALzF0fPbiQ">the guy who used impact client to grief your minecraft base</a>,
<a href="https://whysoserious.jp/">the guy</a> who created <a href="https://en.wikipedia.org/wiki/Peak_experience">needy streamer overload</a>, the guy who got cancelled because they confused <a href="https://en.wiktionary.org/wiki/misgender">him</a> with aidscox (guy who gave aids to coxes), 
the guy who also got cancelled because they confused <a href="https://en.wiktionary.org/wiki/misgender">him</a> with asciibuns (some guy from a <a href="https://discord.gg/polynomers">discord server</a> who faked <a href="https://www.youtube.com/results?search_query=klasky+csupo">autism</a>, did, ocd, age regression, 
bipolar disorder, <a href="https://en.wikipedia.org/wiki/Fantasy">diphalia</a>, being a <a href="https://www.google.com/error">woman</a>, having mammary cancer, ptsd, <a href="https://en.wikipedia.org/wiki/Object_sexuality">schizofrenia</a>, anxiety, depression and down's syndrome).
I'm also the guy who wrote the first version of the <span title="In kindness, there is evil.">BSD</span>, <span title="In evil, there is kindess.">Darwin</span>, <span title="Balance.">Linux</span>, and <span title="But then there's this fucking bitch">NT kernel</span> Thank me for your smart fridge even existing.
I am your father, your mother, your uncle, your aunt, and your seventh grandad. Flintstones.
<br>
<br>
<a href='/'>Home page</a></div>

<?php
$page_content = ob_get_clean();
require_once $_SERVER['DOCUMENT_ROOT'] . '/template.php';
?>