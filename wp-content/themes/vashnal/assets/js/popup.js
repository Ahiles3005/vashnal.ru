let popup = {

    window: undefined,
    closeButton: undefined,
    overlay: undefined,
    body: $( 'body' ),

    open: function( content ) {
        this.body.append( content );
        this.body.addClass( 'fixed' );
        this.window = $( '.popup' );
        this.closeButton = $( '.popup__close' );
        this.overlay = $( '.popup__overlay' );
        this.closeButton.on( 'click', this.close );
        this.overlay.on( 'click', this.close );
    },

    close: function() {
        popup.window.remove();
        popup.overlay.remove();
        popup.body.removeClass( 'fixed' );
    }

};