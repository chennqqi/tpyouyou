function AskReplyCallBack(args){
	if(args.statusCode == 300){
		alertMsg.error(args.message)
		return false;
	}
        if(args.statusCode == 200){
		alertMsg.correct(args.message)
                $.pdialog.closeCurrent();
                location.reload() ;
		return false;
	}      
}