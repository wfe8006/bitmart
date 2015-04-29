module.exports = function(grunt) 
{
	grunt.initConfig(
	{
		
		watch :
		{
		    scripts :
		    {
		    	files : ['js/app.js','*.md','css/*.css', '*.php', '*/**/*.php', 'libraries/**/*.php', 'i18n/**/*.php', 'public/**/*.php', 'pubilc/index.php', 'views/**/*.php', 'classes/**/*.php', 'css/*.css'],
		      	//tasks : ['uglify','markdown']
		      	options : 
		      	{
        			livereload : true
      			}
		    },
		},
	});

	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('default', ['watch']);
};
