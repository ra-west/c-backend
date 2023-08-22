// Создаем алиасы
let Sprite    = PIXI.Sprite,
    loader    = PIXI.Loader.shared,
    Textures  = PIXI.Texture;

class Game {
  constructor() {
    this.app = new PIXI.Application({
      width: 640,
      height: 640,
      resolution: 1,
      //backgroundColor: 0x1099bb,
      transparent: true
    });
    console.log(this.app);

    this.seconds = 0;

    this.numbers = [
      26, 3, 35, 12, 28, 7, 29, 18, 22, 
      9, 31, 14, 20, 1, 33, 16, 24, 5, 10, 
      23, 8, 30, 11, 36, 13, 27, 6, 34, 17, 
      25, 2, 21, 4, 19, 15, 32, 0
    ];

    this.number = null;

    this.spinUpTime = 1;
    this.spinUpInterval = this.spinUpTime;
    this.spinUpSubinterval = 1 / this.spinUpInterval;

    this.stopInterval = 2;
    this.stopSubinterval = 1 / this.stopInterval;

    this.endTime = null;
    this.isPlaying = false;
  }

  configureWheel() {
    this.wheel = new Sprite(this.textures["wheel.png"]);
    this.wheel.anchor.set(0.5);
    this.wheel.scale.set(0.6);
    this.wheel.y = -100;

  }
  configureButtons() {
    this.buttonStart = new Sprite(this.textures["button_normal.png"]);
    this.buttonStop = new Sprite(this.textures["button_normal.png"]);

    this.b_down = Textures.from("button_down.png");
    this.b_normal = Textures.from("button_normal.png");
    this.b_over = Textures.from("button_over.png");

    // Измение кнопок 
    this.buttonStart
      .on('pointerdown',      () => this.onClick(this.buttonStart, true))
      .on('pointerup',        () => this.onClickAfter(this.buttonStart))
      .on('pointerupoutside', () => this.onClickAfter(this.buttonStart))
      .on('pointerover',      () => this.onPointerOver(this.buttonStart))
      .on('pointerout',       () => this.onPointerOut(this.buttonStart));

    this.buttonStop
      .on('pointerdown',      () => this.onClick(this.buttonStop, false))
      .on('pointerup',        () => this.onClickAfter(this.buttonStop))
      .on('pointerupoutside', () => this.onClickAfter(this.buttonStop))
      .on('pointerover',      () => this.onPointerOver(this.buttonStop))
      .on('pointerout',       () => this.onPointerOut(this.buttonStop));

    // Положение кнопок
    this.buttonStart.anchor.set(0.5);
    this.buttonStart.position.set(-100, 200);

    this.buttonStop.anchor.set(0.5);
    this.buttonStop.position.set(100, 200);

    // Добавление действия к кнопкам
    this.buttonStart.interactive = true;
    this.buttonStart.buttonMode = true;

    this.buttonStop.interactive = true;
    this.buttonStop.buttonMode = true;
  };

  configureText() {
    // Натсройка стилей текста
    const style = new PIXI.TextStyle({ fontFamily: 'Arial', fontSize: 36 });

    // Создание текста 
    this.basicText = new PIXI.Text('Start', style);
    this.basicText.anchor.set(0.5);
    this.basicText.position.set(-100, 200);

    this.basicText2 = new PIXI.Text('Stop', style);
    this.basicText2.anchor.set(0.5);
    this.basicText2.position.set(100, 200)

    this.basicText3 = new PIXI.Text(`Score : 0`, style);
    this.basicText3.anchor.set(0.5);
    this.basicText3.position.set(-230, -280)
  }

  configureTriangle() {
    this.graphics = new PIXI.Graphics();

    this.graphics.beginFill(0x32CD32);
    this.graphics.lineStyle(4, 0xF0FFFF, 1);
    this.graphics.moveTo(0, -295);
    this.graphics.lineTo(-20, -315);
    this.graphics.lineTo(20, -315);
    this.graphics.lineTo(0, -295);
    this.graphics.closePath();
    this.graphics.endFill();
  }

  setup() {
    document.getElementById('root').appendChild(this.app.view);

    /*
    anchor - смещает оригинальное положение точек спрайта текстуры от 0 до 1
    pivot  - смещает начало координа х и у спрайта, используя значение пикелей
    */

    this.textures = loader.resources["/sanctum/gamewheel/common/images/spritesheet.json"].textures;

    this.configureWheel();
    this.configureButtons();
    this.configureText();
    this.configureTriangle();

    const container = new PIXI.Container();
    container.addChild(this.wheel);
    container.addChild(this.buttonStart);
    container.addChild(this.buttonStop);
    container.addChild(this.basicText);
    container.addChild(this.basicText2);
    container.addChild(this.basicText3);
    container.addChild(this.graphics);

    container.position.set(this.app.screen.width / 2, this.app.screen.height / 2); // Тоже самое что и две строчки выше
    this.app.stage.addChild(container);

    this.state = this.play;
    this.app.ticker.add((delta) => this.gameLoop(delta));
  }

  gameLoop(delta){
    //Обновление текущего состояния игры
    this.state(delta);
  }

  play(delta) {
    if (!this.isPlaying) {
      return;
    }

    if (this.endTime === null || this.seconds <= this.endTime) {
      // Вращение колеса
      // console.log(delta);
      const f = this.getFrequency();
      this.wheel.angle += 10 * delta * f;
      console.log(`f = ${f}, delta = ${delta}, angle = ${this.wheel.angle}`);
      
      if (f < 0 || f > 1) {
        console.error(`f = ${f}`);
      }
    } else {
      // this.app.ticker.remove(delta);
      this.getNumberOnWheel(this.wheel.angle);
      this.isPlaying = false;
      return;
    }
    
    this.seconds += delta / 60;
  }


  // Нажатие на кнопку
  onClick(object, bool) {
    object.texture = this.b_down;
    //Установка состояния игры
    if (bool) {
      this.endTime = null;
      this.seconds = 0;
      this.stopTime = null;
      // this.state = this.play;
      this.wheel.angle = 0;
      this.isPlaying = true;
      // this.app.ticker.add((delta) => this.gameLoop(delta));
    } else {
      //const nowInSeconds = Math.floor(Date.now() / 1000);
      console.log('Second = ', this.seconds);
      const nowInSeconds = this.seconds;
      this.stopTime = nowInSeconds;
      this.endTime = this.stopTime + this.stopInterval;
      console.log(this.stopTime, this.endTime);

    }
    //Запуск игрового цикла
    
  };

  // Функция отвечающее за состоянии кнопки после нажатия на нее
  onClickAfter(object) {
    object.texture = object.isOver ? this.b_over : this.b_normal;
  }

  // Если курсор находиться на кнопке
  onPointerOver(object) {
    object.isOver = true;
    object.texture = this.b_over;
  }

  // Если курсор находится вне кнопки
  onPointerOut(object) {
    object.isOver = false;
    object.texture = this.b_normal;
  }

  // Возвращает 
  getFrequency() {
    if (this.seconds <= this.spinUpTime) {
      return Game.easeOutSine(this.spinUpSubinterval * this.seconds);
    }
    
    if (this.stopTime !== null && this.seconds >= this.stopTime) {
      return Game.easeOutSine(1 - this.stopSubinterval * (this.seconds - this.stopTime));    // 1 - 0.5 * [0..2]
    }
    
    return 1;
  }

  getNumberOnWheel(wheelAngle) {
    const partOfFullWheel = 360 / (this.numbers.length);
    const qtyFullTurn = Math.floor(wheelAngle / 360); // Количество полных оборотов
    const partOfFullTurn = wheelAngle - (360 * qtyFullTurn); // Какую часть занимает от полного оборота
    console.log(partOfFullWheel);
    this.number = Math.floor(partOfFullTurn / partOfFullWheel);
    this.basicText3.text = (`Score: ${this.numbers[this.number]}`);
    return this.numbers[this.number];
    //console.log(this.numbers[number]);
    // console.log((this.numbers.length) / 360);
    // return this.numbers[
    //   Math.floor(wheelAngle % 360 * this.numbers.length / 360)
    // ];

    
  }

  static easeOutSine(x) {
    return Math.sin((x * Math.PI) / 2);
  }
}

const setup = () => {
  const game = new Game();
  game.setup();
};

loader
  .add("/sanctum/gamewheel/common/images/spritesheet.json")
  .load(setup);
