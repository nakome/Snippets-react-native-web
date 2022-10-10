// await Sleep(1000);
const Sleep = (milliseconds) => {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
  }

export default Sleep;