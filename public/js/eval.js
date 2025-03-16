/*jshint devel:true */
/*global MathJax*/ /*global graderPath*/ /*global scoreKeeperPath*/
/*global submitWrittenAnswerPath*/ /*global qforaPath*/
/*jshint unused:false*/

function sciNotation(value) {
	//var pattern = /(\d|\D)((x|\*)10\^)(\d|-\d)/;	// regex looking for (v)(x or *) 10^ (+/-v);
	var pattern = /(\d|\D)\s*((x|\*)\s*10\^)(\d|-\d)/;	// regex looking for (v)(x or *) 10^ (+/-v);
	if (pattern.test(value)) {
		value = value.replace(pattern, "$1e$4");
	}
	return value;
}

function addArrays(obj, input) {
	console.log('in addArrays');
	console.log(obj);
	console.log(input);
	var length = input.data[input.data.length - 1].data.value.trim().split("\n"); // compute length
	length = length.length;
	for (var j = input.data.length - 1; j >= 0; j--) {
		var arr = input.data[j].data;
		var name = arr.id;
		var split = arr.value.trim().split("\n"); // .trim() removes lagging whitespace, returns,

		if (split.length !== length && split[0] !== "") {
			obj.in_valid = "Array variables must have the same amount of values (check <i>" + arr.labels[0].innerText + "</i>)";
			break;
		}
		else if (split[0] === "") {
			split[0] = 0;
		}

		obj[name] = [];
		for (var k = 0; k < split.length; k++) {
			var num = sciNotation(split[k]);
			if (!isNaN(num)) {
				num = parseFloat(num);
				obj[name].push(num);
			}
			else {
				obj.in_valid = "Array variables must contain only numeric values (check <i>" + arr.labels[0].innerText + "</i>)";
				break;
			}
		}
	}
}

function getAllVars(order)
{
	var obj = {};
	var standards = $(".standard_input");
	var strings = $(".string_input");
	var simples = $(".simple_input");
	var arrays = $(".array_input");

	var inputsInOrder = [];
	for (var i = standards.length - 1; i >= 0; i--) {
		if (! inputsInOrder[standards[i].parentNode.parentNode.id]) {
			inputsInOrder[standards[i].parentNode.parentNode.id] = {type: "standard", data: []};
		}

		inputsInOrder[standards[i].parentNode.parentNode.id].data.push({type: "standard", data: standards[i]});
	}
	for (i = strings.length - 1; i >= 0; i--) {
		if (! inputsInOrder[strings[i].parentNode.id]) {
			inputsInOrder[strings[i].parentNode.id] = {type: "standard", data: []};
		}

		inputsInOrder[strings[i].parentNode.id].data.push({type: "string", data: strings[i]});
	}
	for (i = simples.length - 1; i >= 0; i--) {
		if (! inputsInOrder[simples[i].parentNode.id]) {
			inputsInOrder[simples[i].parentNode.id] = {type: "simple", data: []};
		}

		inputsInOrder[simples[i].parentNode.id].data.push(simples[i]);
	}
	for (i = arrays.length - 1; i >= 0; i--) {
		if (! inputsInOrder[arrays[i].parentNode.parentNode.id]) {
			inputsInOrder[arrays[i].parentNode.parentNode.id] = {type: "array", data: []};
		}

		inputsInOrder[arrays[i].parentNode.parentNode.id].data.push({type: "array", data: arrays[i]});
	}

	console.log(inputsInOrder);
	// console.log(order);
	for (i = 1; i <= order; i++) {
		var input = inputsInOrder[i];
		if (!input) {
			continue;
		}

		var name;
		var j;

		switch (input.type) {
			case "standard":
				var arraysToAdd = {type: "array", data: []};
				for (j = 0; j < input.data.length; j++) {
					var input_data = input.data[j];

					if (input_data.type === "standard") {
						if (input_data.data.value === "") {
							input_data.data.value = 0;
						}

						var val = sciNotation(input_data.data.value);
						if ($.isNumeric(val)) {
							name = input_data.data.id;
							obj[name] = val;
						}
						else {
							obj.in_valid = "Please enter a numeric value for " + input_data.data.title;
							break;
						}
					}
					else if (input_data.type === "string") {
						// no converting null to 0 because "" is a valid string
						if (! $.isNumeric(input_data.data.value)) {
							name = input_data.data.id;
							obj[name] = input_data.data.value;
						}
						else {
							obj.in_valid = "Please enter an alphabetic value for " + input_data.data.title;
							break;
						}
					}
					else if (input_data.type === "array") {
						console.log('array');
						arraysToAdd.data.push(input_data);
					}
				}

				if (arraysToAdd.data.length > 0) {
					addArrays(obj, arraysToAdd);
				}

				break;

			case "simple":
				// console.log(input);
				for (j = 0; j < input.data.length; j++) {
					var simple = input.data[j];
					name = simple.id;
					var value = sciNotation(simple.value);
					obj[name] = value;
				}
				break;

			case "array":
				console.log('array pure');
				addArrays(obj, input);
				break;
		}
	}

	return obj;
}

var evaledQues = [];
var allQues = [];

// Uses an AJAX request with the question name and vars data and displays the results in a fieldset
function evalQuestion(id,order,inters) {
    if(evaledQues.indexOf(id) === -1) {
        evaledQues.push(id);
    }
    var values = getAllVars(order);
    console.log(values);
}

function evalQuestionBak(id, order, inters)
{
	if(evaledQues.indexOf(id) === -1) {
		evaledQues.push(id);
	}
	// console.log(evaledQues);
	var values = getAllVars(order);
	console.log(values);

	// Show the message
	$("#output_" + id).hide();
	if (values.in_valid) {
		$("#message_" + id).html(values.in_valid).show();
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, '"#message_" + id']);
		return false;
	}
	else {
		$("#message_" + id).html("Processing question... ").show();
	}

	$.post(graderPath,
		{
			question: id,
			values: JSON.stringify(values),
		},
		function (grade)
		{
			var grade_obj;
			var markup;
			try {
				grade_obj = JSON.parse(grade);
				grade_obj = grade_obj.filter(function(e) {return e !== undefined;});
				console.log(grade_obj);
			}
			catch (exception){
				console.log(exception);
				console.log(grade);
			}
			if (grade_obj.length) {
				for (var i = 0; i < grade_obj.length; i++)
				{
					var response = grade_obj[i];
					id = response.question;
					markup = "<fieldset>" + response.output + "</fieldset>";
					var output = $("#output_" + id);
					output.html(markup).show();
					$("#message_" + id).hide();
					if (response.score !== null)
					{
						if (parseInt(response.score[0]) === parseInt(response.score[1])) {
							$("#output_" + id).removeClass("alert-info");
							$("#output_" + id).addClass("alert-success");
						}
						else {
							$("#output_" + id).addClass("alert-info");
							$("#output_" + id).removeClass("alert-success");
						}

						markup = "<p>" + response.score[0] + "/" + response.score[1] + " points</p>";
						$("#interVars_" + id).hide();
						$("#score_" + id).html(markup);
					}
					if (inters)
					{
						if (!Array.isArray(response.interVars))
						{
							var vals = "<fieldset><table class='table'><tbody>";
							for (var iv in response.interVars)
							{
								vals += "<tr><td>" + iv + "</td><td>" + response.interVars[iv] + "</td></tr>";
							}
							vals += "</tbody></table></fieldset>";

							$("#interVars_" + id).html(vals).show();
						}
						else {
							$("#interVars_" + id).html("No variables.").show();
						}
					}
				}
			}
			else {
				markup = "<fieldset>" + grade.output + "</fieldset>";
				$("#output_" + id).html(markup).show();
			}

			MathJax.Hub.Queue(["Typeset", MathJax.Hub, '"#output_" + id']);
		}
	);
}

// Scores functions
function getLatestScore(question_id, points) {
	console.log("qid: "+question_id);
	$.post(scoreKeeperPath,
		{
			qid: question_id,
		},
		function (data)
		{
			console.log(data);
			var markup;
			if (points === 0) {
				$("#score_" + question_id).html("<p> 0 points</p>");
			}
			else if (data.length && data[1]) {
				if (evaledQues.indexOf(question_id) === -1) {
					evaledQues.push(question_id);
				}

	            data = JSON.parse(data);
	            if (data.points[0] !== null && data.points[1] !== null) {
					markup = "<p>" + data.points[0] + "/" + data.points[1] + "points" + "</p>";
					$("#score_" + question_id).html(markup);
				}

				if (data.submitted && !data.retry)
	            {
	            	$("#written_answer_"+ question_id).attr("disabled", "disabled");
	            	$("#response_text_"+question_id).show();
	            	$("#prof_response_"+question_id).html(data.result).show();
	            	$("#short_submit_"+ question_id).hide();
	            }
	            else if (data.result !== "" && data.result !== undefined)
	            {
	                markup = "<fieldset>" + data.result + "</fieldset>";
	                $("#output_" + question_id).html(markup).show();
	                if (parseInt(data.points[0]) === parseInt(data.points[1])) {
	                	$("#output_" + question_id).removeClass('alert-info');
	                	$("#output_" + question_id).addClass('alert-success');
	                }

	                MathJax.Hub.Queue(["Typeset", MathJax.Hub, '"#output_" + question_id']);
	            }
	        }
		}
	);
}

function submitWrittenAnswer(question_id)
{
	var sub = $('#written_answer_' + question_id).val();

	$.post(submitWrittenAnswerPath,
		{
			question_id: question_id,
			submission: sub
		},
		function (grade)
		{
			var markup = "<fieldset>" + grade + "</fieldset>";
			$("#output_" + question_id).html(markup).show();
			getLatestScore(question_id);
		}
	);
	$("#written_answer_"+ question_id).attr("disabled", "disabled");
	$("#response_text_"+question_id).show();
	$("#prof_response_"+question_id).show();
	$("#short_submit_"+ question_id).hide();
}

function getLatestScores(assignment_id) {
	$.post(qforaPath,
		{
			aid: assignment_id
		},
		function (data)
		{
			// console.log("result" + data);
            var arr = JSON.parse(data);
            console.log(arr);
			for (var i = 0; i < arr.length; i++)
            {
            	allQues.push(parseInt(arr[i]));
                console.log("Finding " + arr[i]);
				getLatestScore(parseInt(arr[i]));
			}
		}
	);
}
