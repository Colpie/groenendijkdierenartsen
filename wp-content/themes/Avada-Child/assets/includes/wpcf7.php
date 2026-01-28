<?php
// Product options offerte
add_action('wpcf7_init', 'custom_add_form_tag_clock');

function custom_add_form_tag_clock()
{
    wpcf7_add_form_tag('custom_select', 'custom_clock_form_tag_handler');
    wpcf7_add_form_tag('custom_select_2', 'custom_clock_form_tag_handler_2');
    wpcf7_add_form_tag('custom_select_3', 'custom_clock_form_tag_handler_3');
}

function custom_clock_form_tag_handler($tag)
{
    return '
<span class="wpcf7-form-control-wrap menu-150">
<select name="options" id="custom-select" class="wpcf7-form-control wpcf7-select select-option" aria-invalid="false">
<optgroup label="Particulieren">
    <option value="">Kies product</option>
    <option value="Familiale verzekering particulier">Familiale  verzekering</option>
    <option value="Brandverzekering particulier">Brandverzekering</option>
    <option value="Autoverzekering particulier">Autoverzekering</option>
    <option value="Hospitalisatie particulier">Hospitalisatieverzekering</option>
    <option value="Ongevallenverzekering particulier">Ongevallenverzekering</option>
    <option value="Uitvaartverzekering particulier">Uitvaartverzekering</option>
    <option value="Fietsverzekering particulier">Fietsverzekering</option>
    <option value="Dierenverzekering particulier">Dierenverzekering</option>
    <option value="Rechtsbijstand particulier">Rechtsbijstand</option>
    <option value="Reisbijstand particulier">Reisbijstand</option>
</optgroup>
<optgroup label="Ondernemingen">
    <option value="Autoverzekering ondernemen">Autoverzekering</option>
    <option value="Brandverzekering ondernemen">Brandverzekering</option>
    <option value="Ba-uitbating & na-levering ondernemen">BA-uitbating & na-levering</option>
    <option value="Beroepsaansprakelijkheid ondernemen">Beroepsaansprakelijkheid</option>
    <option value="Arbeidsongevallen ondernemen">Arbeidsongevallen</option>
    <option value="Hospitalisatieverzekering ondernemen">Hospitalisatieverzekering</option>
    <option value="Gewaarborgd inkomen ondernemen">Gewaarborgd inkomen</option>
    <option value="Pensioenoplossingen ondernemen">Pensioenoplossingen</option>
    <option value="Rechtsbijstand ondernemen">Rechtsbijstand</option>
    <option value="Reisbijstand ondernemen">Reisbijstand</option>
</optgroup>
</select></span>';
}


// Offerte klein
function custom_clock_form_tag_handler_2($tag)
{
    return '
<span class="wpcf7-form-control-wrap menu-150">
<select name="options_2" id="custom-select_2" class="wpcf7-form-control wpcf7-select select-option" aria-invalid="false">
<optgroup label="Autoverzekering">
    <option value="">Kies een product</option>
    <option value="Burgeijlijke aansprakelijkheid">Burgeijlijke aansprakelijkheid</option>
    <option value="Full omnium">Full omnium</option>
    <option value="Mini omnium">Mini omnium</option>
    <option value="Pechverhelping">Pechverhelping</option>
    <option value="Bestuurdersverzekering">Bestuurdersverzekering</option>
    <option value="BA Pleziervaart">BA Pleziervaart</option>
    <option value="Fietsverzekering">Fietsverzekering</option>
</optgroup>
<optgroup label="Woningverzekering">
    <option value="Brandverzekering">Brandverzekering</option>
    <option value="Diefstal">Diefstal</option>
</optgroup>
<optgroup label="Gezinsverzekering">
    <option value="BA familiale verzekering">BA familiale verzekering</option>
    <option value="Hospitalisatieverzekering">Hospitalisatieverzekering</option>
    <option value="Ongevallenverzekering">Ongevallenverzekering</option>
    <option value="Uitvaartverzekering">Uitvaartverzekering</option>
    <option value="Huispersoneel">Huispersoneel</option>
    <option value="Rechtsbijstandverzekering">Rechtsbijstandverzekering</option>
    <option value="Tandverzekering">Tandverzekering</option>
    <option value="Huisdierenverzekering">Huisdierenverzekering</option>
</optgroup>

<optgroup label="Reisverzekering">
    <option value="Reisverzekering">Reisverzekering</option>
</optgroup>
</select></span>';
}

// Offerte ondernemingen
function custom_clock_form_tag_handler_3($tag)
{
    return '
<span class="wpcf7-form-control-wrap menu-150">
<select name="options_3" id="custom-select_3" class="wpcf7-form-control wpcf7-select select-option" aria-invalid="false">
<optgroup label="Gebouwen">
    <option value="">Kies een product</option>
    <option value="Ondernemingen - Brandverzekering bedrijfsgebouw">Brandverzekering bedrijfsgebouw</option>
    <option value="Ondernemingen - Diefstal van inboedel">Diefstal van inboedel</option>
    <option value="Ondernemingen - Verzekering bedrijfsschade">Verzekering bedrijfsschade</option>
    <option value="Ondernemingen - Verzekering elektronische materiaal">Verzekering elektronische materiaal</option>
    <option value="Ondernemingen - Verzekering machinebreuk">Verzekering machinebreuk</option>
    <option value="Ondernemingen - Rechtsbijstand na brand">Rechtsbijstand na brand</option>
    <option value="Ondernemingen - Geld en waarde">Geld en waarde</option>
    <option value="Ondernemingen - Alle bouwplaats risicos en montagepolis">Alle bouwplaats risicos en montagepolis</option>
</optgroup>
<optgroup label="Aansprakelijkheid">
    <option value="Ondernemingen - Verzekering BA uitbating">Verzekering BA uitbating</option>
    <option value="Ondernemingen - Verzekering BA uitbating - Na levering">Verzekering BA uitbating - Na levering</option>
    <option value="Ondernemingen - Verzekering bestuursaansprakelijkheid of D&O">Verzekering bestuursaansprakelijkheid of D&O</option>
    <option value="Ondernemingen - Verzekering BA vrijwilligers">Verzekering BA vrijwilligers</option>
    <option value="Ondernemingen - Objectieve aansprakelijkheid brand en ontploffing">Objectieve aansprakelijkheid brand en ontploffing</option>
</optgroup>
<optgroup label="Pensioen">
    <option value="Ondernemingen - Vrij aanvullend pensioen zelfstandigen">Vrij aanvullend pensioen zelfstandigen</option>
    <option value="Ondernemingen - Rijksinstituut voor ziekte- en invaliditeitsverzekering">Rijksinstituut voor ziekte- en invaliditeitsverzekering</option>
    <option value="Ondernemingen - Individueel pensioentoezegging">Individueel pensioentoezegging</option>
    <option value="Ondernemingen - Groepsverzekering">Groepsverzekering</option>
</optgroup>
<optgroup label="Voertuigen">
    <option value="Ondernemingen - Wagenparkverzekering">Wagenparkverzekering</option>
    <option value="Ondernemingen - Vrachtwagens">Vrachtwagens</option>
</optgroup>
<optgroup label="Ongeval en ziekte">
    <option value="Ondernemingen - Hospitalisatieverzekering">Hospitalisatieverzekering</option>
    <option value="Ondernemingen - Arbeidsongevallen voor werknemers">Arbeidsongevallen voor werknemers</option>
</optgroup>
<optgroup label="Rechtsbijstand">
    <option value="Ondernemingen - Rechtsbijstandverzekering">Rechtsbijstandverzekering</option>
</optgroup>
</select></span>';
}