define([
	'modules/backbone/models/tasks',
	'modules/backbone/models/equipments'
], function() {
	console.debug('inside model loader', Date());

	_.each(Models, function(e,i){
		var m = e.fetch({'success': function(){
			e = m;
		}});
	});
});
