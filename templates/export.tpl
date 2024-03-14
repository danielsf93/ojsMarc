{**
 * templates/export.tpl
 *}
 {extends file="layouts/backend.tpl"}

 {block name="page"}
	 <h1 class="app__pageHeading">
		 {$pageTitle}
	 </h1>
 
	 <div class="app__contentPanel">
	 
		 <form method="POST" action="{plugin_url path="export"}">
			 <table class="pkpTable">
				 <thead>
					 <tr>
						 <th>{translate key="plugins.importexport.ojsMarc.id"}</th>
						 <th>{translate key="plugins.importexport.ojsMarc.title"}</th>
						 
					 </tr>
				 </thead>
				 <tbody>
					 {foreach $submissions as $submission}
						 <tr>
							 <td>{$submission->getId()}</td>
							 <td>{$submission->getCurrentPublication()->getLocalizedFullTitle()}</td>
							 <td>
								 <input type="radio" name="selectedSubmissionId" value="{$submission->getId()}">
							 </td>
						 </tr>
					 {/foreach}
				 </tbody>
			 </table>
 
			 <button class="pkp_button" type="submit">{translate key="plugins.importexport.ojsMarc.exportSelected"}</button>
		 </form>
		 
	 </div>
 {/block}
 