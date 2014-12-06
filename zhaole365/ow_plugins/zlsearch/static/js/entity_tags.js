$( function() {

	//var _vars = tagsWithCount;
	
});

var EntityTagSelect = function( contextId ){
    this.resultList = [];
    this.$context = $('#'+contextId);
}

EntityTagSelect.prototype = {
    init: function(){
        var self = this;

        $('input.submit',this.$context).click(function(){
            self.submit();
        });
    },

    findIndex: function( value ){

        for( var i = 0; i < this.resultList.length; i++){
            if( value == this.resultList[i] ){
                return i;
            }
        }
        return null;
    },

    reset: function(){
        this.resultList = [];
    },

    submit: function(){
        if( this.resultList.length == 0 )
        {
            //OW.warning(OW.getLanguageText('zlsearch', 'entity_tag_select_empty_list_message'));
        	OW.warning('亲，不想选择标签请点击右上角关闭按钮！');
            return;
        }
        OW.trigger('zlsearch.entity_tag_list_select', [this.resultList]);
    }
}