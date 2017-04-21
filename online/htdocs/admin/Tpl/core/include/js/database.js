
var origin_ajaxbg_text;
var ajaxbg;
var ajaxtip;

//数据库备份
function backup()
{
    origin_ajaxbg_text = $("#progressBar").html();
    alertMsg.confirm(LANG['CONFIRM_BACKUP_DB'], {
        okCall: function(){
            ajaxbg = $("#background,#progressBar");
            ajaxtip = $("#progressBar");
            ajaxtip.html("正在备份数据库，请勿刷新本页，请稍候...");
            ajaxbg.show();
            dump_sql("",1,0,0);
        }
    });
}



function dump_sql(filebase_name,vol,table_key,last_row)
{
	var query = new Object();
	query.vol = vol;
	query.table_key = table_key;
	query.last_row = last_row;
	query.filebase_name = filebase_name;
	query.ajax= 1;
	$.ajax({ 
		url: DUMP_URL,
		data: query,
		dataType: "json",
		global:false,
		success: function(obj){
			if(obj.status)
			{
				if(obj.done==0)
				{
					ajaxtip.html("数据备份中，共有"+obj.table_total+"张表，已备份"+obj.table_key+"张表，共"+(parseInt(obj.vol)-1)+"卷");
					dump_sql(obj.filebase_name,obj.vol,obj.table_key,obj.last_row);
				}	
				else
				{
				    ajaxtip.html(origin_ajaxbg_text);
				    alertMsg.correct("数据备份成功,共"+obj.vol+"卷");
					ajaxbg.hide();
					navTab.reload();
				}
			}
			else
			{
				ajaxtip.html(origin_ajaxbg_text);
                alertMsg.error(obj.info);
                ajaxbg.hide();
			}
		}
		,
		error:function(ajaxobj)
		{
            ajaxtip.html(origin_ajaxbg_text);
            alertMsg.error("数据备份失败");
            ajaxbg.hide();
		}
	});	
}


function restore_db_fun(filename,vol)
{
	$.ajax({ 
		url: RESTORE_URL+"&file="+filename+"&vol="+vol+"&ajax=1", 
		dataType: "json",
		global:false,
		success: function(obj){
			if(obj.status)
			{
				if(obj.done)
				{				    
				    ajaxtip.html(origin_ajaxbg_text);
                    alertMsg.correct("您已成功恢复数据库");
                    ajaxbg.hide();				    
				}
				else
				{
					ajaxtip.html("正在恢复数据库备份"+obj.filename+"_"+obj.vol+".sql");
					restore_db_fun(obj.filename,obj.vol)
				}
			}
			else
			{
				ajaxtip.html(origin_ajaxbg_text);
                alertMsg.error(obj.info);
                ajaxbg.hide();
			}
		}
	,
		error:function(ajaxobj)
		{
	         ajaxtip.html(origin_ajaxbg_text);
             alertMsg.error("数据恢复失败");
             ajaxbg.hide();

		}
	});	
}

function restore_db(filename)
{
	origin_ajaxbg_text = $("#progressBar").html();
    alertMsg.confirm(LANG['CONFIRM_RESTORE_DB'], {
        okCall: function(){
            ajaxbg = $("#background,#progressBar");
            ajaxtip = $("#progressBar");
            ajaxtip.html("正在恢复数据库备份"+filename+"_1.sql，请备刷新本页，请稍候！");
            ajaxbg.show();
            restore_db_fun(filename,1);
        }
    });		
}
