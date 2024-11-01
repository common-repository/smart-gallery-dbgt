(function() {

	function set_shortcodes_atts( editor, atts ) {

		// nom fenetre
		var titreFenetre = !_.isUndefined( atts.nom ) ? atts.nom : 'Ajouter un shortcode';
		// balise du shortcode
		var balise = !_.isUndefined( atts.balise ) ? atts.balise : false;

		fn = function() {
			editor.windowManager.open( {
				title: titreFenetre,
				body: atts.body,
				onsubmit: function( e ) {
					var out = '[' + balise;
					for ( var attr in e.data ) {
						out += ' ' + attr + '="' + e.data[ attr ] + '"';
					}
					out += ']';
					editor.insertContent( out );
				},
			} );
		};
		return fn;
	}

	tinymce.PluginManager.add('ekoflickrdbgt', function( editor, url ) {

		editor.addButton('puipui_dbgt_bouton', {
			icon: true,
			image: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAsQAAALEBxi1JjQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAQdSURBVFiF7ZZtTFV1HMc/5+Geey/3XvFiXJjyEDR58GE1tLnMRWuMMmSTwjFta9qctkrnnC/yFazN1uyF9cItGy1ftOVoqZMMYWy6tZarrLwhRmOCCBjIw5W4cB/OOf9eALfgHuCCLN/4fXXO//f02f/8fuf/h0d6yJKsFmtqauRrN2+d0mxa0VIVMnQ9MBYM7m2q/6rjv+uqlfOVX27krvB4dr5ffdS1VAD13zYal5ovVwEfzAugCVO1O+x6XloQfioHVy4kb1x89dSXSPOlKsIUtpkmS4CYovdh/DYIHZAXD+B5ErBbmh4g69JILat8/T1NU8uQ/mUxdN0xNBRIOnjsfNAW3ZGEbBem5BAJJZRNdm/uUdZm6NPWt2zexOkzdV/H+UcioaNHjlSrsmy5GVPfzHJarNTy+3Uu/PaNWJtxd1qMx+1GFkogDgCEnJv7BBn+fAAij20Hae7WmE0h51PcS89kqEMVM6EvNV/GEMYe4NgMgAnJ4S4QAsf4dZCURQEYSgqQaWkTpolkirhtfvhNmKhjMCTxfZuNqDF7O4Ttg7QO3+DuQETy31ZZl6XT5LdjdHTyZ7uKJLGxpGzHvil/SfBjYgCOTL74zs6tkVXh3Mez53T1ecH39Iv0p+YYxoZN0e62OocxZrJqJVRWlJcCpQCmYXKhoUmfHyD7bUivYLDxOC+XbrEXZ7fAiB/cBeCcCyYAw43s3ZpsYZPAt43zFxtMawBbMnjWgxmB9Irpts5PYORXcOWB07rh5tcEAMzoAaF4kGzJsL4WbN6EUgnVy/2MajxRP0rP50BC/6uYYgA9Rb0YipeMZUHkBIsDjKa/RdSRj5K2DkZ+hr/9CwKIjaGhLJ94UJwLSqCN+dENEzM6AuGeBcXCAsZwNtmH6kkbvYZkjoM+HGcfU12cy9nJ1q5zpIQHZwewh1pAQAgZSYqfdSMaijJ5NozipBcfeZM2OdJrCTeuJPFZwQG6XVl0uXPYd/MjVoTuTQdQZGW8ru7LJFmW5zxw2joGxHPFEJSclOtvkKOk8q7WSV7kDgBB2UG36iM/0hUrXlt4kG5XFgABzcunhYfY33pi2k6ouhEpaWi4WDxXcQCHQ9sNFNREX+BKWCIThQ+9uzg8fIbsaB/Hva/RZfPxzvBZ1uh3qC08ECs+pYDm5dSaQ+xv/ZiUKYDm+rNXgavzAbyya8+zQEGfoQIGAGHJxonlVSSbo/SpEylPel/lGVs33S7rn1RAS+F0/pscnnx/4MMoJGux4gA6Mj9EM0jt/2PWmH5neux5wVOwOsnDhrEBVmpuNCO+66cUHDVZbWtnzLEszqYJHZLyFwYgTFPv6f2LqqLtVPkmF815guKnLqY20Y4QiISvWiXbKp93e9wnZUlyJBozn3TDiLsjPtL/rn8AP5FeAXRzKxIAAAAASUVORK5CYII=',
			text:'Smart Gallery',
			type:'menubutton',
			menu: [
							
				{
					text: 'Gallery Keyword',
					onclick: set_shortcodes_atts( editor, {
						body: [
							{
								label: 'Keyword :',
								name: 'keyword',
								type: 'textbox',
								tooltip: 'Type your keyword :',
								value: '',
							},
							{
								label: 'Image Number',
								name: 'number',
								type: 'listbox',
								values : [
			                        { text: '1', value: '1' },
			                        { text: '2', value: '2' },
			                        { text: '3', value: '3' },
									{ text: '4', value: '4' },
			                        { text: '5', value: '5' },
			                        { text: '6', value: '6' },
									{ text: '7', value: '7' },
			                        { text: '8', value: '8' },
			                        { text: '9', value: '9' },
									{ text: '10', value: '10' },
			                    ]
							},
							{
								label: 'Image Size',
								name: 'imagesize',
								type: 'listbox',
								values : [
									{ text: 'Default', value: 'default' },
			                        { text: 'Large', value: 'large' },
			                        { text: 'Medium', value: 'medium' },
			                        { text: 'Thumb', value: 'thumb' },
			                    ]
							},
							{
								label: 'Legal',
								name: 'legal',
								type: 'listbox',
								values : [
									{ text: 'Default', value: 'default' },
			                        { text: 'Yes With Backlink', value: 'yes' },
									{ text: 'Yes But No Backlink', value: 'yesbis' },
			                        { text: 'No', value: 'no' },
			                    ]
							},
						],
						balise: 'smartgallery_dbgt',
						nom: 'Gallery Keyword',
					} ),
				},
			]
		});
	});

})();