function abc1234()
{

}

//to be used in load_geostore function. In that function, we do not use geo2 as elementId (we use eg: city_town_1, city_town_2 or town_village_1) depending on the result from geo_info table short_name column, so we need to use geo2_name to help us find out the name.
geo1_name = '';
geo2_name = '';


function get_geo()
{
	country = $('#country').val();
	country_name = $('#country option:selected').text()
	city_state = $('#h_city_state').val();
	zip = $('#zip').val();
	neighborhood = '';
	if ($('#neighborhood').val() != '')
	{
		neighborhood = $('#neighborhood option:selected').text()
	}
	geo1 = $('#geo1').val();
	geo1_name = $('#geo1 option:selected').text();
	geo2 = $('#geo2').val();
	geo2_name = $('#geo2 option:selected').text();

	if ($('#listing_location'))
	{
		if (country == "")
		{
			$('#listing_location').val('');
		}
		else
		{
			$('#listing_location').val(country_name);
			if (country == "244")
			{
				if (neighborhood != '' && city_state != '')
				{
					$('#listing_location').val(neighborhood + ', ' + city_state + ' ' + zip + ', ' + country_name);
				}
				if (neighborhood == '' && city_state != '')
				{
					$('#listing_location').val(city_state + ' ' + zip + ', ' + country_name);
				}
			}
			else
			{
				if (country == 131 || country == 171 || country == 192)
				{
					if (geo1 != '' && geo2 != '')
					{
						$('#listing_location').val(geo2_name + ', ' + geo1_name + ', ' + country_name);
					}
					if (geo1 != '' && geo2 == '')
					{
						$('#listing_location').val(geo1_name + ', ' + country_name);
					}
				}
				else
				{
					if ($('#location').val() != '')
					{
						$('#listing_location').val($('#location').val() + ', ' + country_name);
					}
				}
			}
		}
	}
}

$("#country").change(function()
{
	geo2_array = new Array();
	country = $('#country').val();
	h_geo1 = $('#h_geo1').val();
	h_geo2 = $('#h_geo2').val();
	h_neighborhood = $('#h_neighborhood').val();
	
	geo1 = $('#geo1').val();
	geo2 = $('#geo2').val();
	
	$('#geo1')[0].options.length = 1;
	$('#geo2')[0].options.length = 1;
	$('#location').val('');
	//$('#zip').val('');
	if ($('#neighborhood').length > 0)
	{
		$('#neighborhood')[0].options.length = 1;
	}
	//$('#h_neighborhood').val('')
	//$('#h_city_state').val('');
	
	if (country == "")
	{
		$('#us').hide();
		$('#nonus').hide();
	}
	else
	{
		if (country == "244")
		{
			$('#us').show();
			$('#nonus').hide();
			$('#div_location').hide();

			$('#zip').trigger('blur');
			
		}
		else
		{
			$('#us').hide();
			if (country != 244)
			{
				
				if (country > 0)
				{
					geo1_options = "";
					$.getJSON('/json/load_geo', { country: $('#country').val() }, function(data)
					{
						if (data.length > 0)
						{
							$('#g1').show();
							$.each(data, function(geo1_id, element)
							{
								// element = ["Terengganu", Object { i="Besut:589"}, Object { i="Dungun:590"}, Object { i="Hulu Terengganu:591"}, Object { i="Kemaman:592"}, Object { i="Kuala Terengganu:593"}, Object { i="Marang:594"}, Object { i="Setiu:595"}], so length = 8, including Terengganu
								
								geo2_options = "";
								//get geo1-level info, eg: state/province
								geo1_array = element[0].split(':');
								geo1_id = geo1_array[1];
								selected = geo1_id == h_geo1 ? " selected" : "";
								geo1_options += '<option value="' + geo1_array[1] + '"' + selected + '>'+geo1_array[0]+ '</option>';
								//get geo2-level info, eg: city
								geo2_array[geo1_id] = new Array();
								if (element.length > 1)
								{
									$('#g2').show();
									for (i=1; i<element.length; i++)
									{
										//in case of i="Besut:589", geo = "Besut:589" 
										geo_array = element[i].i.split(':');
										name = geo_array[0];
										id = geo_array[1];
										selected = id == h_geo2 ? " selected" : "";
										geo2_options += '<option value="' + id + '"' + selected + '>' + name + '</option>';
									}
								}
								geo2_array[geo1_id].push(geo2_options);
							});
							$('#geo1').append(geo1_options);
							geo2 = $('#geo2').val();
							$('#geo1').trigger('change');
						}
						else
						{
							$('#g1').hide();
							$('#g2').hide();
						}
						$('#nonus').show();
						$('#div_location').hide();
					});
				}
			}
			else
			{
				$('#nonus').hide();
				$('#div_location').show();
			}
		}
	}
	get_geo();

});

$("#geo1").change(function()
{
	$('#h_geo1').val('');
	$('#h_geo2').val('');
	geo1 = $('#geo1').val();
	//if user selects any geo1 value other than the default 'Select One' (undefined)
	if (typeof geo2_array[geo1] === 'undefined')
	{
		$('#g2').hide();
		$('#geo2')[0].options.length = 1;
	}
	else
	{
		$('#g2').show();
		$('#geo2')[0].options.length = 1;
		//jquery is getting all the geo2 info from geo2_array
		$('#geo2').append(geo2_array[geo1]);
		//if geo2 value is labuan/putrajaya etc, we don't want to display geo2
		if ($('#geo2')[0].options.length == 1)
		{
			$('#g2').hide();
		}
	}
	get_geo();
});

$("#geo2").change(function()
{
	get_geo();
});

$('#zip').blur(function(e)
{
	$('#h_city_state').val('');
	if ($('#neighborhood').length > 0)
	{
		$('#neighborhood')[0].options.length = 1;
	
		if ($('#zip').val() == "")
		{
			get_geo();
		}
		else
		{
			$.getJSON('/json/load_citystate', { zip: $('#zip').val() }, function(result){
				if (result != "")
				{
					$.each(result, function(state, city)
					{
						if (state != "")
						{
							$('#h_city_state').val(city + ", " + state);
							$.getJSON('/json/neighborhood', { zip: $('#zip').val() }, function(result){
								neighborhood_options = "";
								$.each(result, function(name, id)
								{
									selected = id == h_neighborhood ? " selected" : "";
									neighborhood_options += '<option value="' + id + '"' + selected + '>' + name + '</option>';
								});
								$('#neighborhood').append(neighborhood_options);
								$('#neighborhood').trigger('change');
							});
					
						}
					});
				}
			});
		}
	}
	
});

$("#neighborhood").change(function()
{
	get_geo();
});

$('#location').blur(function(e)
{
	get_geo();
});


$("body").on("change", ".geo1", function(){ 

	element_id = $(this).attr("id");
	element_array = element_id.split('_');
	geo1_id = $('#' + element_id).val();
	if ($('#' + geo2_name).length > 0)
	{
		//sometimes geo2 is input type=text, we only cater for dropdown
		if ($('#' + geo2_name).prop('type') != 'text')
		{
			$('#' + geo2_name)[0].options.length = 1;
			$('#' + geo2_name).append(geo2_array[geo1_id]);
		}
	}

});


//for store and shipping address
$("#country1").change(function()
{
	
	$('#d_layer').empty();
	if ($('#country1').val() != '')
	{
		$.getJSON('/json/load_geostore', { id: $('#country1').val() }, function(data)
		{
			html = '';
			geo_data = data[0];
			
			
			//need to extract the elementId of geo1+geo2 as the next block needs the elementIds
			$.each(data, function(index, data)
			{
				if (data[5])
				{
					if (data[5] == 'geo1')
					{
						geo1_name = data[3];
					}
					else if (data[5] == 'geo2')
					{
						geo2_name = data[3];
					}
				}
			});

			if (geo_data.length > 0)
			{
				geo1_options = "";
				geo2_array = new Array();
				$.each(geo_data, function(geo1_id, element)
				{
					// element = ["Terengganu", Object { i="Besut:589"}, Object { i="Dungun:590"}, Object { i="Hulu Terengganu:591"}, Object { i="Kemaman:592"}, Object { i="Kuala Terengganu:593"}, Object { i="Marang:594"}, Object { i="Setiu:595"}], so length = 8, including Terengganu
					geo2_options = "";
					//get geo1-level info, eg: state/province
					geo1_array = element[0].split(':');
					geo1_id = geo1_array[1];
					
					if ($('#country1').val() == country_ori)
					{
						
						selected = geo1_id == eval(geo1_name + '') ? " selected" : "";
					}
					else
					{
						selected = '';
					}
					

					geo1_options += '<option value="' + geo1_array[1] + '"' + selected + '>'+geo1_array[0]+ '</option>';
					//get geo2-level info, eg: city
					geo2_array[geo1_id] = new Array();
					if (element.length > 1)
					{
						for (i=1; i<element.length; i++)
						{
							//in case of i="Besut:589", geo = "Besut:589" 
							geo_array = element[i].i.split(':');
							name = geo_array[0];
							id = geo_array[1];
							
							if ($('#country1').val() == country_ori)
							{
								selected = id == eval(geo2_name + '') ? " selected" : "";
							}
							else
							{
							
								selected = '';
							}
							geo2_options += '<option value="' + id + '"' + selected + '>' + name + '</option>';
						}
					}
					geo2_array[geo1_id].push(geo2_options);

				});
			}
			
			data.splice(0, 1);
			
			$.each(data, function(index, data)
			{
				/*
				index0: order
				index1: gid
				index2: name
				index3: short name
				index4: compulsory field
				index5: has_geo? does the field hold info of geo1 (state/province) or geo2(town/city)? It's needed to pull info from geo1/geo2
				*/
				
				if (data[4] == 1)
				{
					symbol = ' *';
					required = ' required';
				}
				else
				{
					symbol = '';
					required = '';
				}
				if (data[5] == 'geo1')
				{
					geo1_name = data[3];
					html += '<div class="spacer row"><label class="col col-lg-3 col-md-3 control-label" for="' + data[3] + '">' + data[2]  + symbol + '</label><div class="col col-lg-5 col-md-5"><select class="form-control geo1' + required + '" id="' + data[3] + '" name="' + data[3] + '"><option value="">-</option>' + geo1_options + '</select></div></div>';
				}
				else if (data[5] == 'geo2')
				{
					geo2_name = data[3];
					html += '<div class="spacer row"><label class="col col-lg-3 col-md-3 control-label" for="' + data[3] + '">' + data[2]  + symbol + '</label><div class="col col-lg-5 col-md-5"><select class="form-control geo2' + required + '" id="' + data[3] + '" name="' + data[3] + '"><option value="">-</option></select></div></div>';
					/*
					$('#geo1').append(geo1_options);
					geo2 = $('#geo2').val();
					$('#geo1').trigger('change');
					$('#g2').show();
					*/
					
					
					/*
					options = '';
					jQuery.each(data[5], function(id, name) {
						options += '<option value="' + id + '">' + name + '</option>'; 
					});
					html += '<div class="spacer row"><label class="col col-lg-3 col-md-3 control-label" for="' + data[3] + '">' + data[2]  + symbol + '</label><div class="col col-lg-5 col-md-5"><select class="form-control' + required + '" id="' + data[3] + '" name="' + data[3] + '">' + options + '</select></div></div>';
					*/
				}
				else
				{
					if ($('#country1').val() == country_ori)
					{
						value = eval(data[3] + '');
					}
					else
					{
						value = '';
					}
					html += '<div class="spacer row"><label class="col col-lg-3 col-md-3 control-label" for="' + data[3] + '">' + data[2]  + symbol + '</label><div class="col col-lg-5 col-md-5"><input class="form-control' + required + '" id="' + data[3] + '" name="' + data[3] + '" type="text" value="' + value + '"></div></div>';
				}
				
			});
			$('#d_layer').html(html);
			$('#' + geo1_name + '').trigger('change');
		});
	}

});


function loadcat(p_ml)
{
	//p_ml = maximum level via parameter
	selected = cl = p_ml;
	nl = ++p_ml;
	//keystore is empty after user clicks "change category", so we have to fetch the data via json

	if (cl > 0)
	{
		if ($('#cat' + cl).val() > 0)
		{
			if (keystore['a' + $('#cat' + (cl - 1)).val()])
			{
				has_child = keystore['a' + $('#cat' + (cl - 1)).val()]['a' + $('#cat' + cl).val()][1];
			}
			else
			{
				option_text = $('#cat' + cl + '>option:selected').text();
				has_child = option_text.charAt(option_text.length - 1) == ">" ? 1 : 0;
			}
		}
		else
		{
			has_child = 0;
		}
	}
	else
	{
		option_text = $('#cat0>option:selected').text();
		has_child = option_text.charAt(option_text.length - 1) == ">" ? 1 : 0;
	}

	if (has_child == 1)
	{
		//$('msg').update("Selected category:");
		$('#breadcrumb').text("Selected category:");
		
		//if category id found in the cache
		if (keystore['a' + $('#cat' + cl).val()])
		{
			options = '<option value="0">Select One</option>';
			for (var i in keystore['a' +  $('#cat' + cl).val()])
			{
				node = keystore['a' +  $('#cat' + cl).val()][i][1] == 1 ? ' >' : '';
				options += '<option value="' + i.substring(1) + '">' + keystore['a' + $('#cat' + cl).val()][i][0] + node + '</option>';
			}
			//console.log('existed keystore options: ' + options);
			//eg: if next level = 2 and deepest level = 4, hiding level 2 to level 4, repopulate level 2 with the cached options
			if (nl <= dl)
			{
				for (var i = nl; i <= dl; i++)
				{
					$('#b' + i).hide();
				}
			}
			//$('#cat'+nl).show() doesn't work
			$('#b' + nl).show();
			$('#cat' + nl).empty().append(options);
		}
		else
		{
			$('#cat' + cl).disabled = true;
			/*
			cl = current level
			nl = next level, concentrate on the current click, eg: if we click on the second list's item that has child, then next Clickable level (nl) = 3, current level (cl) = 2, and deepest level (dl) = 2
			dl = deepest level ever traversed (clicked)
			ml = maximum level
			
			keystore for caching
			eg: Appliance (552) -> Large Appliance(679) -> Dishwashers (681) -> Portable Dishwashers (684)
			we add a dummy 'a' in front so that javascript object/array is not sorting numerically:
			with dummy character:
			this.keystore[19] = Antique
			this.keystore[552] = Appliance
			this.keystore[24] = Books
			
			without dummy character:
			this.keystore[19] = Antique
			this.keystore[24] = Books
			this.keystore[552] = Appliance
			
			this.keystore[a552] = new Object()
			this.keystore[a679] = new Object()
			this.keystore[a681] = new Object()
			......
			
			this.keystore[a552][a713] = ["Heating, Cooling & Air", "1"]
			this.keystore[a552][a679] = ["Large Appliances", "1"]
			this.keystore[a552][a555] = ["Other", "0"]
			this.keystore[a552][a724] = ["Sewing & Ironing", "1"]
			this.keystore[a552][a598] = ["Small Appliances", "1"]
			this.keystore[a552][a723] = ["Vacuums & Floor Care", "1"]
			.....
			
			this.keystore[a679][a680] = ["Cooktops", "0"]
			this.keystore[a679][a681] = ["Dishwashers", "1"]
			this.keystore[a679][a687] = ["Food Waste Disposers", "0"]
			this.keystore[a679][a688] = ["Freezers", "1"]
			.....
			
			this.keystore[a681][a682] = ["Built-In Dishwashers", "0"]
			this.keystore[a681][a683] = ["Convertible Dishwashers", "0"]
			this.keystore[a681][a686] = ["Other", "0"]
			this.keystore[a681][a684] = ["Portable Dishwashers", "0"]
			this.keystore[a681][a685] = ["Specialty Dishwashers", "0"]
			.....
			*/
			
			//if selected value is not "select one"
			if ($("#cat" + cl).val() > 0)
			{
				$.getJSON('/json/loadcat', { cat_id: $("#cat" + cl).val() }, function(data)
				{
					keystore['a' + $('#cat' + cl).val()] = new Object();
					options = '<option value="0">Select One</option>';
					$.each(data, function(geo1_id, element)
					{
						
						node = element.has_child == 1 ? ' >' : '';
						if (element.total)
						{
							options += '<option value="' + element.id + '">' + element.name + ' (' + element.total + ')' + node + '</option>';
						}
						else
						{
							options += '<option value="' + element.id + '">' + element.name + node + '</option>';
						}
						keystore['a' + $('#cat' + cl).val()]['a' + element.id] = new Array(element.name, element.has_child); 
					});

					/*
					eg: if next level = 2 and deepest level = 4, hiding level 2 to level 4, repopulate level 2 with the cached options
					firstly select: Appliance (552) -> Large Appliance(679) -> Dishwashers (681) -> Portable Dishwashers (684)
					then select: Arts and Crafts -> Other,
					this would hide level2 -> level4 of the Appliance category
					*/
					//console.log('next level: ' + nl);
					//console.log('deepest level: ' + dl);
					
					if (nl <= dl)
					{
						for (var i = nl; i <= dl; i++)
						{
							$('#b'+i).hide();
						}
					}
					else
					{
						dl = nl;
					}
					//if #cat+nl doesn't exist
					if ($('#cat'+nl).length == 0)
					{
						($('<div id="b' + nl + '" class="spacer col col-lg-4"><select class="form-control" id="cat'+nl+'" name="cat'+nl+'" size="10">'+options+'</select></div>')).insertAfter($('#b'+cl));
						$('#cat' + nl).change(function(e)
						{
							ii = e.target.name.substring(3);
							loadcat(ii);
						});
					}
					else
					{
						$('#b'+nl).show();
						$('#cat'+nl).empty().append(options);
					}
				});
			}
			else
			{
				if (nl <= dl)
				{
					for (var i = nl; i <= dl; i++)
					{
						$('#b'+i).hide();
					}
				}
				else
				{
					dl = nl;
				}
			}

		}
	}
	else
	{

		if ($("#cat" + cl).val() > 0)
		{
			var msg = "";
			for (i = 0; i <= cl; i++)
			{
				
				option_before = $('#cat'+i + ' option:selected').text();
				cid = $('#cat'+i).val();
				has_child_node = option_before.indexOf(">");
				if (has_child_node > 0)
				{
					option_after = option_before.substring(0, (has_child_node - 1));
					msg += '<li class="active"><a href="/?cid=' + cid + '">' + option_after + '</a></li>';
				}
				else
				{
					option_after = option_before;
					msg += '<li class="active"><a href="/?cid=' + cid + '">' + option_after + '</a></li>';
				}
				
			}
		}
		else
		{
			var msg = "";
		}
		$('#breadcrumb').show();
		$('#breadcrumb').html("<li><b>Selected category:</b></li>" + msg);
		$('#msg').hide();
		//console.log('no child: nl='+nl + '----------- this.dl=' + this.dl);
		//if the current level has no child, hide the rest of the levels (make the <select> style invisible
		if (nl <= dl)
		{
			for (var i = nl; i <= dl; i++)
			{
				$('#b'+i).hide();
			}
		}

	}
}


/* ========================================================================
 * Bootstrap: collapse.js v3.0.0
 * http://twbs.github.com/bootstrap/javascript.html#collapse
 * ========================================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

  // COLLAPSE PUBLIC CLASS DEFINITION
  // ================================

  var Collapse = function (element, options) {
    this.$element      = $(element)
    this.options       = $.extend({}, Collapse.DEFAULTS, options)
    this.transitioning = null

    if (this.options.parent) this.$parent = $(this.options.parent)
    if (this.options.toggle) this.toggle()
  }

  Collapse.DEFAULTS = {
    toggle: true
  }

  Collapse.prototype.dimension = function () {
    var hasWidth = this.$element.hasClass('width')
    return hasWidth ? 'width' : 'height'
  }

  Collapse.prototype.show = function () {
    if (this.transitioning || this.$element.hasClass('in')) return

    var startEvent = $.Event('show.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var actives = this.$parent && this.$parent.find('> .panel > .in')

    if (actives && actives.length) {
      var hasData = actives.data('bs.collapse')
      if (hasData && hasData.transitioning) return
      actives.collapse('hide')
      hasData || actives.data('bs.collapse', null)
    }

    var dimension = this.dimension()

    this.$element
      .removeClass('collapse')
      .addClass('collapsing')
      [dimension](0)

    this.transitioning = 1

    var complete = function () {
      this.$element
        .removeClass('collapsing')
        .addClass('in')
        [dimension]('auto')
      this.transitioning = 0
      this.$element.trigger('shown.bs.collapse')
    }

    if (!$.support.transition) return complete.call(this)

    var scrollSize = $.camelCase(['scroll', dimension].join('-'))

    this.$element
      .one($.support.transition.end, $.proxy(complete, this))
      .emulateTransitionEnd(350)
      [dimension](this.$element[0][scrollSize])
  }

  Collapse.prototype.hide = function () {
    if (this.transitioning || !this.$element.hasClass('in')) return

    var startEvent = $.Event('hide.bs.collapse')
    this.$element.trigger(startEvent)
    if (startEvent.isDefaultPrevented()) return

    var dimension = this.dimension()

    this.$element
      [dimension](this.$element[dimension]())
      [0].offsetHeight

    this.$element
      .addClass('collapsing')
      .removeClass('collapse')
      .removeClass('in')

    this.transitioning = 1

    var complete = function () {
      this.transitioning = 0
      this.$element
        .trigger('hidden.bs.collapse')
        .removeClass('collapsing')
        .addClass('collapse')
    }

    if (!$.support.transition) return complete.call(this)

    this.$element
      [dimension](0)
      .one($.support.transition.end, $.proxy(complete, this))
      .emulateTransitionEnd(350)
  }

  Collapse.prototype.toggle = function () {
    this[this.$element.hasClass('in') ? 'hide' : 'show']()
  }


  // COLLAPSE PLUGIN DEFINITION
  // ==========================

  var old = $.fn.collapse

  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.collapse')
      var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.collapse.Constructor = Collapse


  // COLLAPSE NO CONFLICT
  // ====================

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


  // COLLAPSE DATA-API
  // =================

  $(document).on('click.bs.collapse.data-api', '[data-toggle=collapse]', function (e) {
    var $this   = $(this), href
    var target  = $this.attr('data-target')
        || e.preventDefault()
        || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
    var $target = $(target)
    var data    = $target.data('bs.collapse')
    var option  = data ? 'toggle' : $this.data()
    var parent  = $this.attr('data-parent')
    var $parent = parent && $(parent)

    if (!data || !data.transitioning) {
      if ($parent) $parent.find('[data-toggle=collapse][data-parent="' + parent + '"]').not($this).addClass('collapsed')
      $this[$target.hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
    }

    $target.collapse(option)
  })

}(window.jQuery);


