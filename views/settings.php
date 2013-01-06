<?php
/**
 * The szm_rss_feeder plugin add Wolf CMS with RSS provider.
 *
 * @package wolf
 * @subpackage plugin.szm_rss_feeder
 *
 * @author Maurizio Serrazanetti <info@sezamo.net>
 * @version 0.5.x beta
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 * @copyright Maurizio Serrazanetti, 2011
 */

// Security checks
if( !defined('CMS_BACKEND') || !AuthUser::isLoggedIn() ) {
	die("All your base are belong to us!");
}
?>
<h1><?php echo __('SeZaMo RSS Feeder Settings'); ?></h1>

<form action="<?php echo get_url('plugin/'.SzmRssFeederController::PLUGIN_ID.'/save'); ?>" method="post">
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('RSS Settings'); ?></legend>
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="title"><?php echo __('Title'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $title; ?>" name="title" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('Sets the title of the RSS feeder, as shall appears on XML'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="description"><?php echo __('Description'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $description; ?>" name="description" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('A short description for this RSS feeder, as shall appear on XML'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="relativeUrl"><?php echo __('Relative URL'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $relativeUrl; ?>" name="relativeUrl" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('Sets the relative (to site) url to respond to RSS requests'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="webmaster"><?php echo __('Webmaster'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $webmaster; ?>" name="webmaster" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('Sets the webmaster of the RSS feeder'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="maxitems"><?php echo __('Max items'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $maxitems; ?>" name="maxitems" maxlength="3" style="width: 10%; text-align: right;" />
					<span style="padding-left: 2em; width: 50%; text-align: left;">
						<label for="language"><?php echo __('Language'); ?>:</label>
						<select class="select" id="language" name="language" style="width: 30%;">
<?php					foreach (Setting::getLanguages() as $code => $label): ?>
							<option value="<?php echo $code; ?>"<?php if ($code == $language) echo ' selected="selected"'; ?>><?php echo __($label); ?></option>
<?php					endforeach; ?>
						</select>
					</span>
				</td>
				<td class="help"><?php echo __('Sets the maximum number of post to output and language code'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="parents"><?php echo __('Root pages'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $parents; ?>" name="parents" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('Comma separated list of parent pages to list children'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="categories"><?php echo __('Default categories'); ?>: </label></td>
				<td class="field">
					<input type="text" class="textinput" value="<?php echo $categories; ?>" name="categories" style="width: 100%;" />
				</td>
				<td class="help"><?php echo __('Comma separated list of categories that will always be set on RSS header'); ?></td>
			</tr>
		</table>
	</fieldset>
	<br/>
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
		<?php echo __('or'); ?> <a href="<?php echo get_url('plugin/'.SzmRssFeederController::PLUGIN_ID); ?>"><?php echo __('Cancel'); ?></a>
	</p>
</form>
