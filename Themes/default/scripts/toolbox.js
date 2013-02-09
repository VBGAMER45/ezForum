// Had to make another file for this, as the default one allows multiple selections, we dont
// we overwrite the old suggestion with new choices
function smf_ToolBox(oOptions)
{
	this.opt = oOptions;
	this.oToAutoSuggest = null;
	this.sPreviousSelect = null;
	this.oToListContainer = null;
	this.init();
}

smf_ToolBox.prototype.init = function()
{
	var oToControl = document.getElementById(this.opt.sToControlId);
	this.oToAutoSuggest = new smc_AutoSuggest({
		sSelf: this.opt.sSelf + '.oToAutoSuggest',
		sSessionId: this.opt.sSessionId,
		sSessionVar: this.opt.sSessionVar,
		sSuggestId: this.opt.sSuggestId,
		sControlId: this.opt.sToControlId,
		sSearchType: 'member',
		sPostName: this.opt.sPostName,
		iMinimumSearchChars: 2,
		sURLMask: 'action=profile;u=%item_id%',
		sTextDeleteItem: this.opt.sTextDeleteItem,
		bItemList: true,
		sItemListContainerId: this.opt.sContainer,
		sItemTemplate: '<input type="hidden" name="%post_name%" value="%item_id%" /><a href="%item_href%" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">%item_name%</a>&nbsp;<img src="%images_url%/pm_recipient_delete.gif" alt="%delete_text%" title="%delete_text%" onclick="return %self%.deleteAddedItem(%item_id%);" /> ' + this.opt.sTextViewItem,
		aListItems: this.opt.aToRecipients
	});
	this.oToAutoSuggest.registerCallback('onBeforeAddItem', this.opt.sSelf + '.callbackAddItem');
}

// Prevent more than one item from being added
smf_ToolBox.prototype.callbackAddItem = function(oAutoSuggestInstance, sSuggestId)
{	
	if (this.sPreviousSelect != null)
		this.oToAutoSuggest.deleteAddedItem(this.sPreviousSelect);
	this.sPreviousSelect = sSuggestId
	return true;
}