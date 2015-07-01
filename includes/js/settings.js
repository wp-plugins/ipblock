jQuery(document).ready(function($){
	
	var IPBlock_Settings = function () {


		this.isInt= function(value) {
			return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value))
		}

		this.tableInit = function() {

			var scheme=$('#scheme_input').attr('value');
			scheme = scheme.replace(/\s+/g, ''); //remove empty spaces
			scheme = scheme.replace(/;\s*$/, ""); //remove last semicolon	
			var pairs=scheme.split(';');
			var scheme_table='<table id="scheme_table"><tr><th>Attempts</th><th>Delay</th><th></th></tr>';
	
			pairs.forEach(function(pair) {
				pair=pair.split('=>');
				scheme_table+='<tr class="scheme-pair"><td><input type="text" value="'+pair[0]+'" class="scheme-attempts" /><p>or more</p></td>';
				scheme_table+='<td><input type="text" value="'+pair[1]+'" class="scheme-delay"/><p>seconds</p></td>';
				scheme_table+='<td class="remove" title="remove row">'+PHP.icon_close+'</td></tr>';
			});
	
			scheme_table+='</table>';
			$('#scheme_wrapper').append(scheme_table);
			
			var add_button='<button class="button">Add row</button>';
			$('#scheme_wrapper').append(add_button);
		}
		
		this.addRow = function() {
			var row='<tr class="scheme-pair"><td><input type="text" value="" class="scheme-attempts" /><p>or more</p></td>';
			row+='<td><input type="text" value="" class="scheme-delay"/><p>seconds</p></td><td class="remove" title="remove row">'+PHP.icon_close+'</td></tr>';
			$('#scheme_table').append(row);
		}
		
		this.updateScheme = function() {
			
			var limit=100000000;			
			var scheme='';
			
			$('#scheme_table tr.scheme-pair').each(function() {
				var error=0;

				var attempts=$(this).find('input.scheme-attempts').attr('value');
				var delay=$(this).find('input.scheme-delay').attr('value');
				
				if(!ipblock_settings.isInt(attempts) && attempts<limit) {
					$(this).find('input.scheme-attempts').addClass('error');
					error=1;
				}
				if(!ipblock_settings.isInt(delay) && delay<limit) {
					$(this).find('input.scheme-delay').addClass('error');
					error=1;
				}		
				if (error===0) { 
					scheme+=attempts+'=>'+delay+';';
					$(this).find('input').removeClass('error');
				}		
			});
			
			$('#scheme_input').attr('value',scheme);
			
		}
		
		$('#scheme_input, .scheme-description').hide();
		$('#scheme_input').parents('tbody').find('tr:first-child').hide();
		
		this.tableInit();
		
		$('#scheme_wrapper').on('change','#scheme_table input', function() { ipblock_settings.updateScheme(); });
		$('#scheme_wrapper').on('click','button.button', function() { ipblock_settings.addRow(); return false; });
		$('#scheme_wrapper').on('click','#scheme_table td.remove > svg', function() { $(this).parent().parent().detach(); ipblock_settings.updateScheme(); });
	
	}

	ipblock_settings = new IPBlock_Settings();



});
