jQuery(document).ready(function($) {
	
	// global reference to active game object for handling events
	var _this = undefined;

	$.fn.animateHighlight = function(highlightColor, duration) {
		var highlightBg = highlightColor || "#FFFF9C";
		var animateMs = duration || 1500;
		
		// skip to the end of any current animation
		this.finish();

		// begin the new animation
		var originalBg = this.css("backgroundColor");; 
		this.css("background-color", highlightBg)
			.animate({backgroundColor: originalBg}, animateMs);
		setTimeout( function() { notLocked = true; }, animateMs);
	};

	function setActiveGame(game_class) {
		_this = new game_class($("header.entry-header"), $("div.entry-content"));

		_this.init();
	}

	// Really, JavaScript? You can't provide a basic formatting function?
	function pad(num, size) {
		num = String(num);
		if (size <= num.length) { return num; }
		var s = "000000000" + num;
		return s.substr(s.length - size);
	}

	class GenderGame {
		constructor(title_area, play_area) {
			this.current_streak = [0]; // hack so these are passed by reference
			this.best_streak    = [0];
			this.title_area     = title_area;
			this.play_area      = play_area;
			this.current_noun   = undefined;

			this.score_area_entries = [
				["best-streak", "HI: ", this.best_streak],
				["current-streak", "", this.current_streak]
			];

			this.control_button_values = [
				["masc", "Firinscneach"],
				["fem", "Baininscneach"]
			]
		}

		init() {
			// clear play area HTML
			this.play_area.html("");
			// this.title_area.html("");

			this.play_area.append(this.createScoreArea());
			this.play_area.append(this.createNounArea());
			this.play_area.append(this.createControlArea());

			this.fetchRandomNoun();

			this.updateNounArea();

			this.updateScoreArea();
		}

		updateScoreArea() {
			for (var i in _this.score_area_entries) {
			 	var id = _this.score_area_entries[i][0];
			 	var list_element = $("#" + id);				
				var value_area = list_element.find(".value");
			 	value_area.text(pad(_this.score_area_entries[i][2], 4));
			}
		}

		updateNounArea() {
			$("#noun").text(this.current_noun.noun);
		}

		fetchRandomNoun() {
			$.ajax({
				url: "/wp-json/grammadach-na-gaeilge/v1/noun",
				context: document.body,
				async: false
			}).done(function(data) {
				_this.current_noun = data;
			});
		}

		handleUserChoice(e) {
			if (e.srcElement.id === _this.current_noun.gender) {
				_this.current_streak[0]++;
				
				if (_this.current_streak[0] > _this.best_streak[0]) {
					_this.best_streak[0] = _this.current_streak[0];
				}

				$(e.srcElement).animateHighlight("green", 1500);
			} else {
				_this.current_streak[0] = 0;
				
				$(e.srcElement).animateHighlight("red", 1500);
			}

			_this.fetchRandomNoun();

			_this.updateNounArea();

			_this.updateScoreArea();
		}

		createNounArea() {
			var noun_area = document.createElement("div");
			noun_area.setAttribute("id", "noun-area");

			var noun = document.createElement("span");
			noun.setAttribute("id", "noun");

			var translation = document.createElement("span");
			translation.setAttribute("id", "translation");

			noun_area.append(noun);
			noun_area.append(translation);

			return noun_area;		
		}

		createControlArea() {
			var control_area = document.createElement("div");
			control_area.setAttribute("id", "control-area");

			for (var i in this.control_button_values) {
				control_area.append(
					this.createControlButton(...this.control_button_values[i])
				);
			}

			return control_area;
		}

		createScoreArea() {
			var score_area = document.createElement("div");
			score_area.setAttribute("id", "score-area");

			var score_area_list = document.createElement("ul");

			for (var i in this.score_area_entries) {
				score_area_list.append(
					this.createScoreAreaEntry(...this.score_area_entries[i])
				);				
			}

			score_area.append(score_area_list);

			return score_area;
		}

		createScoreAreaEntry(id, label, value) {
			var entry = document.createElement("li");
			entry.setAttribute("id", id);

			var label_element = document.createElement("span");
			label_element.innerHTML = label;

			var value_element = document.createElement("span");
			value_element.setAttribute("class", "value");
			value_element.innerHTML = value[0];

			entry.append(label_element);
			entry.append(value_element);

			return entry;
		}

		createControlButton(id, value) {
			var btn = document.createElement("a");
			btn.setAttribute("id", id);
			btn.setAttribute("class", "btn btn-default");
			btn.innerHTML = value;

			$(btn).click(this.handleUserChoice);

			return btn;
		}
	};

	class MainMenu {
		constructor(title_area, play_area) {
			this.title_area = title_area;
			this.play_area = play_area;

			this.options = {
				"Identifying Noun Gender" : GenderGame
			}
		}

		init() {
			// clear play area HTML
			this.play_area.html("");
			// this.title_area.html("");

			for (var key in this.options) {
				this.play_area.append(
					'<a href="#" id="switch-to-game">' + key + '</a><br />'
				);
			}

			$('a#switch-to-game').click(this, this.switch_to_game);
		}

		switch_to_game(e) {
			e.preventDefault(); 

			setActiveGame(e.data.options[this.text]);
		}
	}

	setActiveGame(GenderGame);
});