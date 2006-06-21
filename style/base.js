/* Initialize / Finalize *****************************************************/
var tt_base       = new Object();
var tt_init_funcs = new Array();
var tt_fini_funcs = new Array();

tt_base =
{
  init: function()
  {
    for (var i = 0; i < tt_init_funcs.length; i++) tt_init_funcs[i]();
  },

  finish: function()
  {
    for (var i = 0; i < tt_fini_funcs.length; i++) tt_fini_funcs[i]();
  }
};

window.onload = tt_base.init;
window.unload = tt_base.finish;